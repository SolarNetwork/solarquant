package net.solarquant.neuralnet;

import java.io.File;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.TimeZone;
import org.apache.commons.lang.time.DateUtils;
import net.solarquant.database.Request;

public class EmergentTrainingManager extends TrainingManager{
	private static final String ENGINE_NAME = "emergent";

	public EmergentTrainingManager() {
		super(ENGINE_NAME);
	}

	@Override
	protected boolean hasManagedProcessRunComplete(Request r) {
		String loc = location + "/../../prediction_output/correlations/" + r.getRequestId()+ "_correlation.csv";
		if(new File(loc).exists()) {
			return true;
		}
		return false;
	}

	@Override
	protected boolean startManagedProcess(Request r) {
		ProcessBuilder pb = new ProcessBuilder("python", "RunEmergent.py", "-r", ""+r.getRequestId());
		pb.directory(new File(location + "/../../emergent/python/src"));

		try {
			Process p = pb.start();
			return true;
		} catch ( IOException e ) {

			e.printStackTrace();
		} 

		return false;

	}

	@Override
	protected boolean verifyStoredData(Request r) {
		Date lastDate = db.getLatestTrainingDataDate(r);
		if ( lastDate == null ) {
			return false;
		}

		Date cDate = new Date();

		if ( DateUtils.isSameDay(lastDate, cDate) ) {
			return true;
		} else {
			return false;
		}
	}

	@Override
	protected void updateStoredData(Request r) {
		Date lastDate = db.getLastDatumCreatedDate(r);
		Date cDate = new Date();
		SimpleDateFormat formatter = new SimpleDateFormat("yyyy-M-dd:HH:mm");
		formatter.setTimeZone(TimeZone.getTimeZone("UTC"));
		
		ProcessBuilder pb;
		if ( lastDate == null ) {
			//if is first time retrieving data for this node/source, do not set start/end parameters
			pb = new ProcessBuilder("python", "DatabasePopulator.py", "-r", "" + r.getRequestId());

		} else {

			String start = formatter.format(lastDate);
			String end = formatter.format(cDate);
			logger.debug(end);
			pb = new ProcessBuilder("python", "DatabasePopulator.py", "-r", "" + r.getRequestId(), "-s",
					start, "-e", end);
		}

		
		pb.directory(new File(location + "/../../data_retrieval"));
		logger.info("running data retrieval python at location: " + location + "/../../data_retrieval");
		logger.info("command = " + pb.command());

		try {

			Process p = pb.start();

		} catch ( IOException e ) {
			logger.error("ERROR:", e);
		}
	}

}
