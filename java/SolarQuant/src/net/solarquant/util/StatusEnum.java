package net.solarquant.util;



/**
 * Representation of the states that a training/prediction request can exist in.
 * TODO REPLACE WITH CHAR REP IN DATABASE
 * @author matthew
 *
 */
public enum StatusEnum {


	INITIAL(1), RETRIEVING_DATA(2), RUNNING(3), FINISHED(4);

	private int stateId_;
	StatusEnum(int i) {

		stateId_ = i;

	}

	public int getStateId() {
		return stateId_;
	}

	//generate an enum from an integer input
	public static StatusEnum fromInt(int i) {
		for (StatusEnum e : StatusEnum.values()) {
			if (e.stateId_ == i) {
				return e;		        
			}
		}
		return null;
	}

}
