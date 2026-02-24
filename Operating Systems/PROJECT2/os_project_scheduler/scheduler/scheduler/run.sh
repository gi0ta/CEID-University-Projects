./scheduler_v1 FCFS 1 homogeneous.txt   > fcfs_1_homogeneous.txt
./scheduler_v1 FCFS 2 homogeneous.txt   > fcfs_2_homogeneous.txt
./scheduler_v1 FCFS 1 reverse.txt > fcfs_1_reverse.txt
./scheduler_v1 FCFS 2 reverse.txt > fcfs_2_reverse.txt

./scheduler_v1 RR 1 homogeneous.txt 1000 > rr1000_1_homogeneous.txt
./scheduler_v1 RR 1 reverse.txt 1000 > rr1000_1_reverse.txt
./scheduler_v1 RR 2 homogeneous.txt 1000 > rr1000_2_homogeneous.txt
./scheduler_v1 RR 2 reverse.txt 1000 > rr1000_2_reverse.txt

./scheduler_v1 RR-AFF 1 homogeneous.txt 1000 > rr-aff1000_1_homogeneous.txt
./scheduler_v1 RR-AFF 1 reverse.txt 1000 > rr1000_1_reverse.txt
./scheduler_v1 RR-AFF 2 homogeneous.txt 1000 > rr1000_2_homogeneous.txt
./scheduler_v1 RR-AFF 2 reverse.txt 1000 > rr1000_2_reverse.txt

