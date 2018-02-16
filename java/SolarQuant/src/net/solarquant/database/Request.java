
package net.solarquant.database;

import java.sql.Date;
import net.solarquant.util.StatusEnum;

/**
 * This class represents a user request for training or predictions, and
 * encapsulates the data, and allows for status updates
 * 
 * @author matthew
 *
 */
public class Request {

	private DBHandler d = new DBHandler();

	private Date date_;
	private String engine_;
	private StatusEnum status_;
	private int requestId_, nodeId_;
	private String sourceId_;
	private Type type_;
	private boolean dynamic_;
	
	public static enum Type{
		
		PREDICTION("prediction"), TRAINING("training");
		String name_;
		
		Type(String name) {
			name_ = name;
		}
		
		public String getName() {
			return name_;
		}
	}


	public Request(Date date, String engine, int status, 
			int requestId, int nodeId, String sourceId, Type type, boolean dynamic) {

		date_ = date;
		engine_ = engine;
		status_ = StatusEnum.fromInt(status);
		requestId_ = requestId;
		nodeId_ = nodeId;
		sourceId_ = sourceId;
		type_ = type;
		dynamic_ = dynamic;

	}

	public Date getRequestDate() {
		return date_;
	}

	public String getEngineName() {
		return engine_;
	}

	public StatusEnum getStatus() {
		return status_;
	}

	public int getRequestId() {
		return requestId_;
	}

	public int getNodeId() {
		return nodeId_;
	}

	public String getSourceId() {
		return sourceId_;
	}
	
	public Type getType() {
		return type_;
	}
	
	public boolean isDynamic() {
		return dynamic_;
	}
	

	public void updateStatus(StatusEnum newStatus) {
		if(type_ == Type.TRAINING) {
			d.updateRequestStatus("training_requests", requestId_, newStatus.getStateId());
			d.logNewStateDateTime("training_state_time", requestId_, newStatus.getStateId());
		}else if(type_ == Type.PREDICTION){
			d.updateRequestStatus("prediction_requests", requestId_, newStatus.getStateId());
			d.logNewStateDateTime("prediction_state_time", requestId_, newStatus.getStateId());
		}
		
		

	}

}
