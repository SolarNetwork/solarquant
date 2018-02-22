
package net.solarquant.database;

import java.sql.Connection;
import java.sql.Date;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.Timestamp;
import java.util.Calendar;
import net.solarquant.util.StatusEnum;

/**
 * 
 * Database handler class contains specific methods relating to request data and state.
 * 
 * <p>TODO </p>
 * 
 * @author matthew
 * @version 1.0
 */
public class DBHandler {

	private static final String CONNECTION_STRING = "jdbc:mysql://localhost/solarquant?"
			+ "user=solarquant&password=solarquant";

	private Connection conn_;

	public DBHandler() {
		getConnection();
	}

	private void getConnection() {
		try {
			conn_ = DriverManager.getConnection(CONNECTION_STRING);
		} catch ( SQLException ex ) {
			System.out.println("SQLException: " + ex.getMessage());
		}
	}

	// gets the oldest request, as the requests are processed in FIFO queue
	public Request getOldestRequest(String tableName, StatusEnum status, boolean dynamic) {
		Statement stmt = null;
		try {
			String query;
			
			query = dynamic ? "SELECT * FROM %s WHERE STATUS = %s AND DYNAMIC = 1 " + "ORDER BY DATE_REQUESTED ASC LIMIT 1":
				"SELECT * FROM %s WHERE STATUS = %s " + "ORDER BY DATE_REQUESTED ASC LIMIT 1";
								;
			query = String.format(query, tableName, status.getStateId());
			stmt = conn_.createStatement();
			ResultSet rs = stmt.executeQuery(query);

			if ( rs.next() == false ) {
				return null;
			} else {
				if(tableName.contains("training")) {
					return new Request(rs.getDate("DATE_REQUESTED"), rs.getString("REQUEST_ENGINE"),
							rs.getInt("STATUS"), rs.getInt("REQUEST_ID"), rs.getInt("NODE_ID"),
							rs.getString("SOURCE_ID"),Request.Type.TRAINING, rs.getBoolean("DYNAMIC"));
				}else {
					return new Request(rs.getDate("DATE_REQUESTED"), rs.getString("REQUEST_ENGINE"),
							rs.getInt("STATUS"), rs.getInt("REQUEST_ID"), rs.getInt("NODE_ID"),
							rs.getString("SOURCE_ID"),Request.Type.PREDICTION, rs.getBoolean("DYNAMIC"));
				}
			}

		} catch ( SQLException e ) {
			e.printStackTrace();
		}
		return null;

	}

	//changes the current status to the new status specified in input
	public void updateRequestStatus(String tableName, int reqId, int newStatus) {
		Statement stmt = null;
		try {
			String query = "UPDATE %s SET STATUS = " + newStatus + " WHERE REQUEST_ID = %s";
			query = String.format(query, tableName, reqId);
			stmt = conn_.createStatement();
			stmt.execute(query);

		} catch ( SQLException e ) {
			e.printStackTrace();
		}
	}

	public void logNewStateDateTime(String tableName, int reqId, int status) {

		Statement stmt = null;
		Timestamp out = new Timestamp(System.currentTimeMillis());
		try {
			String query = "INSERT INTO %s VALUES(%s, %s, '%s', %s)";
			query = String.format(query, tableName, reqId, status, out, null);
			stmt = conn_.createStatement();
			stmt.execute(query);
		}catch( SQLException e ) {
			e.printStackTrace();			
		}
	}

	// gets the most recent date for the addition of new data for a specific node+source
	// prevents the unnecessary downloading of existing data
	public Timestamp getLatestTrainingDataDate(Request r) {
		String query = "SELECT ENTRY_DATE FROM %s WHERE NODE_ID = %s AND SOURCE_ID = '%s' "
				+ "ORDER BY ENTRY_DATE DESC LIMIT 1";

		String tableName = r.getType().getName() + "_input";

		query = String.format(query, tableName, r.getNodeId(), r.getSourceId());
		Statement stmt;
		try {
			stmt = conn_.createStatement();
			ResultSet rs = stmt.executeQuery(query);

			if ( rs.next() == true ) {
				Timestamp out = rs.getTimestamp("ENTRY_DATE");
				Timestamp t = new Timestamp(out.getTime() + Calendar.getInstance().getTimeZone().getOffset(out.getTime()));
				return t;
			} else {
				return null;
			}

		} catch ( SQLException e ) {
			e.printStackTrace();
			return null;
		}

	}

	public java.util.Date getLastDatumCreatedDate(Request r) {
		String query = "SELECT DATE_CREATED FROM %s WHERE NODE_ID = %s AND SOURCE_ID = '%s' "
				+ "ORDER BY DATE_CREATED DESC LIMIT 1";

		query = String.format(query, "node_datum", r.getNodeId(), r.getSourceId());
		Statement stmt;
		try {
			stmt = conn_.createStatement();
			ResultSet rs = stmt.executeQuery(query);

			if ( rs.next() == true ) {
				Timestamp out = rs.getTimestamp("DATE_CREATED");
				Timestamp t = new Timestamp(out.getTime() + Calendar.getInstance().getTimeZone().getOffset(out.getTime()));
				return t;
			} else {
				return null;
			}

		} catch ( SQLException e ) {
			e.printStackTrace();
			return null;
		}
	}
	
	public Timestamp getStateCompletedDate(Request r, StatusEnum e) {
		String query = "SELECT COMPLETION_DATE FROM %s WHERE REQUEST_ID = %s AND STATE = %s "
				+ "ORDER BY COMPLETION_DATE DESC LIMIT 1";
		if(r.getType() == Request.Type.TRAINING) {
			query = String.format(query, "training_state_time", r.getRequestId(), e.getStateId());
		}else {
			query = String.format(query, "prediction_state_time", r.getRequestId(), e.getStateId());
		}
		Statement stmt;
		try {
			stmt = conn_.createStatement();
			ResultSet rs = stmt.executeQuery(query);

			if ( rs.next() == true ) {
				Timestamp out = rs.getTimestamp("COMPLETION_DATE");
				Timestamp t = new Timestamp(out.getTime());
				return t;
			} else {
				return null;
			}

		} catch ( SQLException ex ) {
			ex.printStackTrace();
			return null;
		}
	}


	public boolean checkPredictionComplete(Request r) {
		String query = "SELECT * FROM prediction_output WHERE REQUEST_ID = %s";
		query = String.format(query, r.getRequestId());

		Statement stmt;
		try {
			stmt = conn_.createStatement();
			ResultSet rs = stmt.executeQuery(query);
			if ( rs.next() == true ) {
				return true;
			}else {
				return false;
			}

		} catch ( SQLException e ) {
			return false;
		}
	}
	
	
	public boolean checkTrainingComplete(Request r) {
		String query = "SELECT * FROM training_correlation WHERE REQUEST_ID = %s";
		query = String.format(query, r.getRequestId());	
		
		Statement stmt;
		try {
			stmt = conn_.createStatement();
			ResultSet rs = stmt.executeQuery(query);
			return rs.next();

		} catch ( SQLException e ) {
			return false;
		}
	}
	
	
}

















