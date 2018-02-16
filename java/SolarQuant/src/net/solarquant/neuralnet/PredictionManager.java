
package net.solarquant.neuralnet;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.net.URISyntaxException;
import java.time.LocalDate;
import java.time.Period;
import java.util.Properties;
import org.apache.log4j.Logger;
import org.apache.log4j.PropertyConfigurator;
import net.solarquant.database.DBHandler;
import net.solarquant.database.Request;
import net.solarquant.res.Resource;
import net.solarquant.util.StatusEnum;

public abstract class PredictionManager implements NetManager{
	protected static final String PREDICTION_TABLE = "prediction_requests";
	private String engineName_;
	protected Logger logger = Logger.getLogger(PredictionManager.class);
	protected String location;
	protected DBHandler db = new DBHandler();

	public PredictionManager(String engineName) {
		engineName_ = engineName;

		InputStream log4jConf = Resource.class.getResourceAsStream("log4j.properties");
		Properties prop = new Properties();

		try {

			prop.load(log4jConf);
			PropertyConfigurator.configure(prop);
			logger.info(engineName_ + " - Prediction Manager started");

		} catch ( IOException e ) {
			logger.error("ERROR:", e);
		}
		try {

			location = new File(TrainingManager.class.getProtectionDomain().getCodeSource().getLocation()
					.toURI().getPath()).getParent();

		} catch ( URISyntaxException e ) {

			logger.error("ERROR:", e);

		}
	}

	@Override
	public void manageJobs() {

		int reqId;
		String engine;

		//First check for running jobs - highest priority
		logger.info(engineName_ + " - Checking running jobs...");
		Request rd = db.getOldestRequest(PREDICTION_TABLE, StatusEnum.RUNNING, false);

		if ( rd != null ) {

			reqId = rd.getRequestId();
			engine = rd.getEngineName();

			if ( hasManagedProcessRunComplete(rd) && engine.equalsIgnoreCase(engineName_) ) {

				rd.updateStatus(StatusEnum.FINISHED);

			} else {
				return;
			}
		}

		//Next check for requests that are in retrieving data state.
		logger.info(engineName_ + " - Checking data retrieval jobs...");
		rd = db.getOldestRequest(PREDICTION_TABLE, StatusEnum.RETRIEVING_DATA, false);
		if ( rd != null ) {
			reqId = rd.getRequestId();
			engine = rd.getEngineName();
			if ( verifyStoredData(rd) && engine.equalsIgnoreCase(engineName_) ) {

				logger.info(engineName_ + " - Stored data verified up to date.");
				logger.info(engineName_ + " - Starting training");
				boolean success = startManagedProcess(rd);

				if ( success ) {
					logger.info(engineName_ + " - Successfully started training");
					rd.updateStatus(StatusEnum.RUNNING);
					return;
				}
			} else {
				return;
			}
		}

		//next check for jobs in initial state to progress
		logger.info(engineName_ + " - Checking inital state jobs...");
		rd = db.getOldestRequest(PREDICTION_TABLE, StatusEnum.INITIAL, false);

		if ( rd != null && rd.getEngineName().equalsIgnoreCase(engineName_)) {

			if ( !verifyStoredData(rd)) {
				updateStoredData(rd);

				logger.info(engineName_ + " - Begun retrieving data.");

				rd.updateStatus(StatusEnum.RETRIEVING_DATA);
				return;

			} else {
				logger.info(engineName_ + " - Stored data verified up to date.");
				logger.info(engineName_ + " - Starting prediction");

				reqId = rd.getRequestId();
				engine = rd.getEngineName();

				boolean success = startManagedProcess(rd);

				if ( success ) {
					logger.info("Successfully started prediction");
					rd.updateStatus(StatusEnum.RUNNING);
				}
			}

		}
		logger.info(engineName_ + " - Checking for dynamic jobs to restart...");
		rd = db.getOldestRequest(PREDICTION_TABLE, StatusEnum.FINISHED, true);

		if ( rd != null && rd.getEngineName().equalsIgnoreCase(engineName_)) {			
			Period diff = Period.between(db.getStateCompletedDate(rd, StatusEnum.RUNNING).toLocalDateTime().toLocalDate(), LocalDate.now());
			
			if(rd.isDynamic()) {
				if(diff.getDays() >= 1)
					rd.updateStatus(StatusEnum.INITIAL);
			}
			
		}
		
	}	

	protected abstract boolean hasManagedProcessRunComplete(Request r);

	protected abstract boolean startManagedProcess(Request r);

	protected abstract boolean verifyStoredData(Request r);

	protected abstract void updateStoredData(Request r);


}
