package net.solarquant.neuralnet;

import java.io.File;
import java.io.IOException;
import net.solarquant.database.Request;

public class Sample extends TrainingManager{
	
	static String engineName = "tensorflow";
	
	public Sample() {
		super(engineName);
	}

	@Override
	protected boolean hasManagedProcessRunComplete(Request r) {
				
		return new File("asdaqsd").exists();
	}

	@Override
	protected boolean startManagedProcess(Request r) {
		ProcessBuilder p = new ProcessBuilder("python", "-r", "4");
		
		try {
			p.start();
		} catch ( IOException e ) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		return false;
	}

	@Override
	protected boolean verifyStoredData(Request r) {
		// TODO Auto-generated method stub
		return false;
	}

	@Override
	protected void updateStoredData(Request r) {
		// TODO Auto-generated method stub
		
	}

}
