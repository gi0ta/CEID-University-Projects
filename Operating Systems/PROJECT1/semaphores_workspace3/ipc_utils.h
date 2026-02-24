#ifndef IPC_UTILS_H
#define IPC_UTILS_H

#include <semaphore.h>
#include <fcntl.h>
#include <stdio.h>
#include <stdlib.h>
#include <errno.h>

static inline sem_t* create_semaphore(const char* name, unsigned int value) {
    sem_t* sem = sem_open(name, O_CREAT | O_EXCL, 0644, value);
    if (sem == SEM_FAILED) {
        if (errno == EEXIST) {
            sem = sem_open(name, 0);
            if (sem == SEM_FAILED) {
                perror("sem_open failed");
                exit(EXIT_FAILURE);
            }
        } else {
            perror("sem_open failed");
            exit(EXIT_FAILURE);
        }
    }
    return sem;
}

static inline void destroy_semaphore(const char* name, sem_t* sem) {
    if (sem_close(sem) == -1) {
        perror("sem_close failed");
    }
    if (sem_unlink(name) == -1) {
        perror("sem_unlink failed");
    }
}

static inline void semaphore_down(sem_t* sem) {
    if (sem_wait(sem) == -1) {
        perror("sem_wait failed");
        exit(EXIT_FAILURE);
    }
}

static inline void semaphore_up(sem_t* sem) {
    if (sem_post(sem) == -1) {
        perror("sem_post failed");
        exit(EXIT_FAILURE);
    }
}

#endif // IPC_UTILS_H
