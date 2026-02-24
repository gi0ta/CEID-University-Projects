#include "ipc_utils.h"
#include <pthread.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>

sem_t* boat_queue;
sem_t* passenger_queue;
sem_t* mutex;

int max_seats;
int total_passengers;
void* passenger_thread(void* arg);

void* boat_thread(void* arg) {
    int boat_capacity = *(int*)arg;

    while (1) {
        semaphore_down(boat_queue);
        semaphore_down(mutex);

        printf("Boat is returning.\n");
        max_seats = boat_capacity;

        semaphore_up(mutex);

        for (int i = 0; i < boat_capacity; i++) {
            semaphore_up(passenger_queue);
        }
    }

    return NULL;
}

void initialize_semaphores(int total_passengers, int boat_capacity) {
    boat_queue = create_semaphore("/boat_queue", 0);
    passenger_queue = create_semaphore("/passenger_queue", total_passengers);
    mutex = create_semaphore("/mutex", 1);
}

void destroy_semaphores() {
    destroy_semaphore("/boat_queue", boat_queue);
    destroy_semaphore("/passenger_queue", passenger_queue);
    destroy_semaphore("/mutex", mutex);
}

int main() {
    srand(time(NULL));

    int num_boats, boat_capacity;

    printf("Enter total passengers: ");
    scanf("%d", &total_passengers);
    printf("Enter number of boats: ");
    scanf("%d", &num_boats);
    printf("Enter boat capacity: ");
    scanf("%d", &boat_capacity);

    max_seats = boat_capacity;

    initialize_semaphores(total_passengers, boat_capacity);

    pthread_t boats[num_boats];
    pthread_t passengers[total_passengers];

    for (int i = 0; i < num_boats; i++) {
        pthread_create(&boats[i], NULL, boat_thread, &boat_capacity);
    }

    int ids[total_passengers];
    for (int i = 0; i < total_passengers; i++) {
        ids[i] = i + 1;
        pthread_create(&passengers[i], NULL, passenger_thread, &ids[i]);
    }

    for (int i = 0; i < total_passengers; i++) {
        pthread_join(passengers[i], NULL);
    }

    printf("All passengers have been transported. Exiting.\n");

    destroy_semaphores();

    return 0;
}
