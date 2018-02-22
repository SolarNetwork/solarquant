#!/bin/bash
export PATH=/bin:/usr/bin:/usr/local/bin
export LD_LIBRARY_PATH=/lib:/usr/lib:/usr/local/lib
echo "emergent -nogui -ni -proj /var/www/html/solarquant/emergent/good.proj batches=1 log_dir=log log_file_nm=/var/www/html/solarquant/emergent/log/logFile1.txt tag=test1 input_weights_file=/var/www/html/solarquant/emergent/weights/weights_$2_$3.wts output_weights_file= input_file=/var/www/html/solarquant/emergent/python/src/training/inputs/input_$1 output_file=/var/www/html/solarquant/emergent/python/test_out/out_$1.csv mode=question" > $4

emergent_svn -nogui -ni -proj /var/www/html/solarquant/emergent/good.proj epochs=50 log_dir=progress_logs log_file_nm=/var/www/html/solarquant/logs/progress_logs/$1_log.csv tag=test1 input_weights_file= output_weights_file=/var/www/html/solarquant/emergent/weights/weights_$2_$3 input_file=/var/www/html/solarquant/emergent/python/src/training/inputs/input_$1 output_file=/var/www/html/solarquant/emergent/output/huhu mode=train  &> $4
