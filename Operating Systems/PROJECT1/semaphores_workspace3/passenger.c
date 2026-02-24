#include "ipc_utils.h"
#include <pthread.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

extern sem_t* passenger_queue;
extern sem_t* boat_queue;
extern sem_t* mutex;
extern int max_seats;

void* passenger_thread(void* arg) {
    int id = *(int*)arg;

    while (1) {
        semaphore_down(passenger_queue); // Περιμένει τη σειρά του
        semaphore_down(mutex); // Κλείδωμα για ασφαλή πρόσβαση στις θέσεις

        // Έλεγχος για αλλαγή γνώμης
        if (rand() % 20 == 0) { // 5% πιθανότητα αλλαγής γνώμης
            printf("Passenger %d changed their mind and is moving to the back of the queue.\n", id);
            
            semaphore_up(mutex);        // Απελευθέρωση του mutex
            semaphore_up(passenger_queue); // Επιστροφή θέσης στην ουρά
            
            sleep(1); // Προαιρετική καθυστέρηση για πιο ομαλή αναμονή
            continue; // Επιστροφή στην αρχή του βρόχου
        }

        // Επιτυχής επιβίβαση
        printf("Passenger %d is boarding.\n", id);
        max_seats--;

        if (max_seats == 0) { // Αν γεμίσει η λέμβος
            printf("Boat is departing.\n");
            semaphore_up(boat_queue); // Ενημέρωση ότι η λέμβος αναχωρεί
        }

        semaphore_up(mutex); // Απελευθέρωση του mutex
        break; // Έξοδος από τον βρόχο γιατί ο επιβάτης επιβιβάστηκε
    }

    return NULL;
}

