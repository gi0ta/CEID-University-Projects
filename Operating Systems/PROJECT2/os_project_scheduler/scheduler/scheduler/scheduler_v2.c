/*
#Κωνσταντίνος Αλεξίου, 1058083
#Παναγιώτα Γκιριτζιώνη, 1070953 
#Αχιλλέας Πλιάτσικας, 1070946
#Γεώργιος Τσάμης, 1084659
*/
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <sys/types.h>
#include <sys/time.h>
#include <time.h>
#include <sys/wait.h>
#include <unistd.h>

#define FCFS 0
#define RR 1
#define RR_AFF 2

#define MAX_LINE_LENGTH 80
#define PROC_NEW    0
#define PROC_STOPPED    1
#define PROC_RUNNING    2
#define PROC_EXITED    3

typedef struct proc_desc {
    struct proc_desc *next;
    char name[80];
    int pid;
    int status;
    double t_submission, t_start, t_end;
    int assigned_processor;           
    int required_processors;        
    int allocated_processors[32];     
} proc_t;


typedef struct single_queue 
{
    proc_t *first;
    proc_t *last;
    long members;
} queue_t;

int policy = 0;       
int quantum = 100;    
queue_t *queues;      
proc_t **running_procs; 

int num_processors;   
double global_t;


void proc_queue_init(queue_t *queue) 
{
    queue->first = queue->last = NULL;
    queue->members = 0;
}


void proc_to_rq_end(queue_t *queue, proc_t *proc) 
{
    if (queue->first == NULL) {
        queue->first = queue->last = proc;
    } else {
        queue->last->next = proc;
        queue->last = proc;
    }
    proc->next = NULL;
    queue->members++;
}


proc_t *proc_rq_dequeue(queue_t *queue) 
{
    proc_t *proc = queue->first;
    if (proc) {
        queue->first = proc->next;
        queue->members--;
    }
    return proc;
}

double proc_gettime() {
    struct timeval tv;
    gettimeofday(&tv, NULL);
    return (double)(tv.tv_sec + tv.tv_usec / 1000000.0);
}

void err_exit(char *msg) 
{
    printf("Error: %s\n", msg);
    exit(1);
}


int allocate_processors_fcfs(proc_t *proc) {
    int allocated = 0;

    for (int i = 0; i < num_processors && allocated < proc->required_processors; i++) {
        if (running_procs[i] == NULL) {
            running_procs[i] = proc; 
            proc->allocated_processors[allocated++] = i;
        }
    }

    if (allocated != proc->required_processors) {
        for (int i = 0; i < allocated; i++) {
            running_procs[proc->allocated_processors[i]] = NULL;
        }
        return 0; 
    }

    return 1; 
}



void release_processors_fcfs(proc_t *proc) {
    for (int i = 0; i < proc->required_processors; i++) {
        int processor_id = proc->allocated_processors[i];
        running_procs[processor_id] = NULL; 
        proc->allocated_processors[i] = -1; 
    }
}


void fcfs(queue_t *queue, int processor_id) {
    proc_t *proc;

    while ((proc = proc_rq_dequeue(queue)) != NULL) {
        if (proc->status == PROC_NEW) {
            if (!allocate_processors_fcfs(proc)) {
                printf("Cannot allocate processors for process: %s\n", proc->name);
                proc_to_rq_end(queue, proc); 
                continue;
            }

   
            proc->t_start = proc_gettime();
            int pid = fork();
            if (pid == -1) {
                err_exit("fork failed!");
            }
            if (pid == 0) {
                printf("Executing %s\n", proc->name);
                execl(proc->name, proc->name, NULL);
                exit(0);
            } else {
                proc->pid = pid;
                proc->status = PROC_RUNNING;

        
                int status;
                waitpid(proc->pid, &status, 0);
                proc->status = PROC_EXITED;
                proc->t_end = proc_gettime();

           
                printf("PID %d - CMD: %s\n", pid, proc->name);
                printf("\tElapsed time = %.2lf secs\n", proc->t_end - proc->t_submission);
                printf("\tExecution time = %.2lf secs\n", proc->t_end - proc->t_start);
                printf("\tWorkload time = %.2lf secs\n", proc->t_end - global_t);
            }

            release_processors_fcfs(proc); 
        }
    }

    printf("Processor %d completed all tasks.\n", processor_id);
}



void sigchld_handler(int signo, siginfo_t *info, void *context) {
    pid_t pid = info->si_pid;
    int status;

    if (waitpid(pid, &status, WNOHANG) > 0) {
        for (int i = 0; i < num_processors; i++) {
            if (running_procs[i] && running_procs[i]->pid == pid) {
                running_procs[i]->status = PROC_EXITED;
                running_procs[i]->t_end = proc_gettime();
                if (policy == RR) {
                    printf("PID %d - CMD: %s\n", running_procs[i]->pid, running_procs[i]->name);
                    printf("\tElapsed time = %.2lf secs\n", running_procs[i]->t_end - running_procs[i]->t_submission);
                    printf("\tExecution time = %.2lf secs\n", running_procs[i]->t_end - running_procs[i]->t_start);
                    printf("\tWorkload time = %.2lf secs\n", running_procs[i]->t_end - global_t);
                }
                printf("Process %d completed.\n", pid);
                running_procs[i] = NULL;
                break;
            }
        }
    }
}





void rr(queue_t *queue, int processor_id) {
    while (1) {
        proc_t *proc = proc_rq_dequeue(queue);
        if (!proc) break; 

        if (proc->status == PROC_NEW) {
            proc->t_start = proc_gettime();
            int pid = fork();
            if (pid == -1) {
                err_exit("fork failed!");
            }
            if (pid == 0) {
                printf("Executing %s on processor %d\n", proc->name, processor_id);
                execl(proc->name, proc->name, NULL);
                exit(0);
            } else {
                proc->pid = pid;
                proc->status = PROC_RUNNING;
                running_procs[processor_id] = proc;

                double start_time = proc_gettime();
                while ((proc_gettime() - start_time) < (quantum / 1000.0)) {
                    int status;
                    if (waitpid(proc->pid, &status, WNOHANG) > 0) {
                        proc->status = PROC_EXITED;
                        proc->t_end = proc_gettime();
                        running_procs[processor_id] = NULL;
                        printf("Process %d completed.\n", proc->pid);
                        break;
                    }
                    usleep(1000);
                }

                if (proc->status == PROC_RUNNING) {
                    kill(proc->pid, SIGSTOP);
                    proc->status = PROC_STOPPED;
                    proc_to_rq_end(queue, proc);
                }
            }
        } else if (proc->status == PROC_STOPPED) {
            kill(proc->pid, SIGCONT);
            proc->status = PROC_RUNNING;
            running_procs[processor_id] = proc;

            double start_time = proc_gettime();
            while ((proc_gettime() - start_time) < (quantum / 1000.0)) {
                int status;
                if (waitpid(proc->pid, &status, WNOHANG) > 0) {
                    proc->status = PROC_EXITED;
                    proc->t_end = proc_gettime();
                    running_procs[processor_id] = NULL;
                    printf("Process %d completed.\n", proc->pid);
                    break;
                }
                usleep(1000);
            }

            if (proc->status == PROC_RUNNING) {
                kill(proc->pid, SIGSTOP);
                proc->status = PROC_STOPPED;
                proc_to_rq_end(queue, proc);
            }
        }
    }

    printf("Processor %d completed all tasks.\n", processor_id);
}


void rr_aff(queue_t *queue, int processor_id) {
    proc_t *proc;
    struct timespec req, rem;

    req.tv_sec = quantum / 1000;
    req.tv_nsec = (quantum % 1000) * 1000000;

    while (1) {
        proc = proc_rq_dequeue(queue);
        while (proc != NULL && proc->assigned_processor != -1 && proc->assigned_processor != processor_id) {
            proc_to_rq_end(queue, proc); 
            proc = proc_rq_dequeue(queue);
        }
        /*
        if (proc == NULL) {
            printf("Processor %d completed all tasks.\n", processor_id);
            break; // Τερματισμός αν δεν υπάρχουν διαθέσιμες διεργασίες
        }*/

        if (proc->assigned_processor == -1) {
            proc->assigned_processor = processor_id;
        }

        if (proc->status == PROC_NEW) {
            proc->t_start = proc_gettime();
            int pid = fork();
            if (pid == -1) {
                err_exit("fork failed!");
            }
            if (pid == 0) {
                printf("executing %s\n", proc->name);
                execl(proc->name, proc->name, NULL);
                exit(0);
            } else {
                proc->pid = pid;
                proc->status = PROC_RUNNING;
                running_procs[processor_id] = proc;
                printf("process %d begins on processor %d\n", pid, processor_id);
            }
        } else if (proc->status == PROC_STOPPED) {
            proc->status = PROC_RUNNING;
            running_procs[processor_id] = proc;
            
            kill(proc->pid, SIGCONT);
        }

        nanosleep(&req, &rem);

        if (proc->status == PROC_RUNNING) {
            kill(proc->pid, SIGSTOP);
            proc->status = PROC_STOPPED;
            
            running_procs[processor_id] = NULL;
            proc_to_rq_end(queue, proc);
        } else if (proc->status == PROC_EXITED) {
            proc->t_end = proc_gettime();
            running_procs[processor_id] = NULL;

          
            printf("process %d ends on processor %d\n", proc->pid, processor_id);
            printf("PID %d - CMD: %s\n", proc->pid, proc->name);
            printf("\tElapsed time = %.2lf secs\n", proc->t_end - proc->t_submission);
            printf("\tExecution time = %.2lf secs\n", proc->t_end - proc->t_start);
            printf("\tWorkload time = %.2lf secs\n", proc->t_end - global_t);
        }
    }
}



int main(int argc, char **argv) {
    if (argc < 4) {
        err_exit("Usage: ./scheduler <policy> <num_processors> <input_file> [quantum for RR]");
    }

    if (!strcmp(argv[1], "FCFS")) {
        policy = FCFS;
    } else if (!strcmp(argv[1], "RR")) {
        policy = RR;
        quantum = atoi(argv[4]);
    } else if (!strcmp(argv[1], "RR-AFF")) {
        policy = RR_AFF; 
        quantum = atoi(argv[4]);
    } else {
        err_exit("Invalid usage");
    }

    num_processors = atoi(argv[2]);
    if (num_processors <= 0) {
        err_exit("Invalid number of processors.");
    }

    FILE *input = fopen(argv[3], "r");
    if (!input) {
        err_exit("Failed to open input file.");
    }

    queues = malloc(num_processors * sizeof(queue_t));
    running_procs = calloc(num_processors, sizeof(proc_t *));
    for (int i = 0; i < num_processors; i++) {
        proc_queue_init(&queues[i]);
    }

    char exec[80];
    int required_processors; 
    int current_queue = 0;

    while (fscanf(input, "%s %d", exec, &required_processors) != EOF) {
        // Έλεγχος αριθμού επεξεργαστών
        if (required_processors > num_processors) {
            printf("Error: Process %s requests more processors (%d) than available (%d).\n",
                   exec, required_processors, num_processors);
            continue; 
        }

        proc_t *proc = malloc(sizeof(proc_t));
        if (!proc) {
            err_exit("Memory allocation failed for process.");
        }

        strcpy(proc->name, exec);
        proc->status = PROC_NEW;
        proc->t_submission = proc_gettime();
        proc->required_processors = required_processors > 0 ? required_processors : 1;
        proc->assigned_processor = -1;
        proc_to_rq_end(&queues[current_queue], proc);
        current_queue = (current_queue + 1) % num_processors; // Κυκλική κατανομή
    }
    fclose(input);


    struct sigaction sa;
    sa.sa_flags = SA_SIGINFO;
    sa.sa_sigaction = sigchld_handler;
    sigaction(SIGCHLD, &sa, NULL);

    global_t = proc_gettime();

    for (int i = 0; i < num_processors; i++) {
        if (fork() == 0) {
            if (policy == FCFS) {
                fcfs(&queues[i], i);
            } else if (policy == RR) {
                rr(&queues[i], i);
            } else if (policy == RR_AFF) {
                rr_aff(&queues[i], i);
            }
            exit(0);
        }
    }

    for (int i = 0; i < num_processors; i++) {
        wait(NULL);
    }

    printf("WORKLOAD TIME: %.2lf secs\n", proc_gettime() - global_t);


    free(queues);
    free(running_procs);

    return 0;
}