
package net.solarquant.neuralnet;


import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.TimeZone;
import org.apache.commons.lang.time.DateUtils;
import net.solarquant.database.Request;



/**
 * Tensorflow Training Manager class first tests if the oldest training process has
 * completed. After this, it proceeds to start a new one in the inital state if
 * it exists.
 * 
 * @author matthew
 *
 */
public class TensorflowTrainingManager extends TrainingManager{
	
	private static final String ENGINE_NAME = "tensorflow";
	
	public TensorflowTrainingManager() {
		super(ENGINE_NAME);
	}
	

	//checks if stored data is up to date with the current day's datum
	@Override
	protected boolean verifyStoredData(Request r) {
		Date lastDate = db.getLatestTrainingDataDate(r);
		
		logger.debug(lastDate);
		
		if ( lastDate == null ) {
			return false;
		}
		Date cDate = new Date();
		
		logger.debug(cDate);
		logger.debug(lastDate);



		if ( DateUtils.isSameDay(lastDate, cDate) ) {
			return true;
		} else {
			return false;
		}

	}

	//returns true if the data is already up to date, else sets process to start downloading and exits.
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

	//looks for output model with request Id number as indication of training completion.
	@Override
	protected boolean hasManagedProcessRunComplete(Request r) {
		String modelLocation = location + "/../../tensorflow/SolarQuant/src/trained_models/" + r.getNodeId()
				+"_" + r.getSourceId()+ "_model.h5";
		if ( new File(modelLocation).exists() ) {
			return true;
		}
		return false;
	}

	//starts the QuantExecutor entry point to begin training
	@Override
	protected boolean startManagedProcess(Request r) {

			ProcessBuilder pb = new ProcessBuilder("python", "QuantExecutor.py", "-r", "" + r.getRequestId());
			pb.directory(new File(location + "/../../tensorflow/SolarQuant/src/"));

			try {
				Process p = pb.start();
			} catch ( IOException e ) {
				logger.error("ERROR:", e);
				return false;

			}

		return true;
	}


}
