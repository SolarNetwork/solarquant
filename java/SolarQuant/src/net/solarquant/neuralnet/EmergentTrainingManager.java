package net.solarquant.neuralnet;

import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.text.SimpleDateFormat;
import java.util.Date;
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

		ProcessBuilder pb;
		if ( lastDate == null ) {
			//if is first time retrieving data for this node/source, do not set start/end parameters
			pb = new ProcessBuilder("python", "DatabasePopulator.py", "-r", "" + r.getRequestId());

		} else {

			String start = new SimpleDateFormat("yyyy-M-dd:HH:mm").format(lastDate);
			String end = new SimpleDateFormat("yyyy-M-dd:HH:mm").format(cDate);
			pb = new ProcessBuilder("python", "DatabasePopulator.py", "-r", "" + r.getRequestId(), "-s",
					start, "-e", end);
		}

		logger.info(pb.command());
		pb.directory(new File(location + "/../../data_retrieval"));
		logger.info("running data retrieval python at location: " + location + "/../../data_retrieval");

		try {

			Process p = pb.start();


		} catch ( IOException e ) {
			logger.error("ERROR:", e);
		}	
	}

}
