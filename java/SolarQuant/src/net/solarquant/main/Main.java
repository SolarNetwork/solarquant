package net.solarquant.main;

import net.solarquant.neuralnet.*;

public class Main {

	public static void main(String[] args) {

		new TensorflowTrainingManager().manageJobs();
		new EmergentTrainingManager().manageJobs();
		new TensorflowPredictionManager().manageJobs();
		new EmergentPredictionManager().manageJobs();
		
		
		
	}
	
}
