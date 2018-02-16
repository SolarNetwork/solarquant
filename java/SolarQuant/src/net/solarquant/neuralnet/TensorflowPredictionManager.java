package net.solarquant.neuralnet;

import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.text.SimpleDateFormat;
import java.util.Arrays;
import java.util.Date;
import org.apache.commons.lang.time.DateUtils;
import net.solarquant.database.Request;

public class TensorflowPredictionManager extends PredictionManager{


	private static final String engineName = "tensorflow";
	public TensorflowPredictionManager() {
		super(engineName);
	}

	@Override
	protected boolean hasManagedProcessRunComplete(Request r) {
	
		if ( db.checkPredictionComplete(r) ) {
			return true;
		}
		return false;
	}

	@Override
	protected boolean startManagedProcess(Request r) {
		ProcessBuilder pb = new ProcessBuilder("python", "QuantExecutor.py", "-r", "" + r.getRequestId(), "-p");
		pb.directory(new File(location + "/../../tensorflow/SolarQuant/src/"));

		try {
			Process p = pb.start();
			InputStream i = p.getErrorStream();
			InputStreamReader ir = new InputStreamReader(i);
			BufferedReader b = new BufferedReader(ir);
			String l;
			try {
				p.waitFor();
			} catch ( InterruptedException e ) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		} catch ( IOException e ) {
			logger.error("ERROR:", e);
			return false;
		}

		return true;
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
			pb = new ProcessBuilder("python", "PredictionPopulator.py", "-r", "" + r.getRequestId());

		} else {

			String start = new SimpleDateFormat("yyyy-M-dd:HH").format(lastDate);
			String end = new SimpleDateFormat("yyyy-M-dd:HH").format(cDate);
			pb = new ProcessBuilder("python", "PredictionPopulator.py", "-r", "" + r.getRequestId(), "-s",
					start, "-e", end);
		}
		pb.directory(new File(location + "/../../data_retrieval"));
		logger.info("running data retrieval python at location: " + location + "/../../data_retrieval");
		logger.info(pb.command());

		try {

			Process p = pb.start();


		} catch ( IOException e ) {
			logger.error("ERROR:", e);
		}

	}

}
