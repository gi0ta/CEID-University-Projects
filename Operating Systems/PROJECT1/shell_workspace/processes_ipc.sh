#!/bin/bash

# Function for inserting data
insert_data() {
    # Καθορισμός του αρχείου εξόδου
    OUTPUT_FILE="passenger_data.csv"

    echo "Enter the full path of the file or press Enter to input data manually:"
    read file_path

    # Αν ο χρήστης δεν δώσει αρχείο, κάνουμε εισαγωγή από πληκτρολόγιο
    if [[ -z "$file_path" ]]; then
        echo "Enter data in the format: [code],[fullname],[age],[country],[status (Passenger/Crew)],[rescued (Yes/No)]"
        echo "Press Ctrl+D when finished."
        while IFS= read -r line; do
            # Καθαρισμός από \r (για πιθανές εισαγωγές από Windows)
            line=$(echo "$line" | tr -d '\r')

            # Έλεγχος μορφής δεδομένων
            if [[ ! "$line" =~ ^[^,]+,[^,]+,[0-9]+,[^,]+,(Passenger|Crew),(Yes|No)$ ]]; then
                echo "Invalid format: $line"
            else
                echo "$line" >> "$OUTPUT_FILE"
            fi
        done
    else
        # Εισαγωγή από αρχείο
        if [[ -f "$file_path" ]]; then
            while IFS= read -r line; do
                # Καθαρισμός από \r (για Windows αρχεία)
                line=$(echo "$line" | tr -d '\r')

                # Έλεγχος μορφής δεδομένων
                if [[ ! "$line" =~ ^[^,]+,[^,]+,[0-9]+,[^,]+,(Passenger|Crew),(Yes|No)$ ]]; then
                    echo "Invalid format in file: $line"
                else
                    echo "$line" >> "$OUTPUT_FILE"
                fi
            done < "$file_path"
        else
            echo "The file does not exist or the path is incorrect."
            exit 1
        fi
    fi
    echo "Data successfully inserted into $OUTPUT_FILE."
}



# Function for searching a passenger
search_passenger() {
    # Βρες τον φάκελο όπου βρίσκεται το script
    SCRIPT_DIR=$(dirname "$(realpath "$0")")
    FILE_PATH="$SCRIPT_DIR/passenger_data.csv"

    # Έλεγχος αν υπάρχει το αρχείο
    if [[ ! -f "$FILE_PATH" ]]; then
        echo "Error: $FILE_PATH does not exist."
        exit 1
    fi

    # Ζήτηση από τον χρήστη για αναζήτηση
    echo "Enter the name or surname of the passenger to search:"
    read search_query

    # Αναζήτηση με grep
    result=$(grep -i "$search_query" "$FILE_PATH")

    # Έλεγχος αν υπάρχουν αποτελέσματα
    if [[ -z "$result" ]]; then
        echo "No matching passenger found."
    else
        echo "Passenger(s) found:"
        echo "$result"
    fi
}

update_passenger() {
    # Ορισμός διαδρομής για το αρχείο
    FILE_PATH="./passenger_data.csv"

    # Έλεγχος αν υπάρχει το αρχείο
    if [[ ! -f "$FILE_PATH" ]]; then
        echo "Error: $FILE_PATH does not exist."
        exit 1
    fi

    # Ζήτηση από τον χρήστη του κωδικού ή του ονόματος/επωνύμου
    echo "Enter the code, name, or surname of the passenger to update:"
    read search_query

    # Εύρεση της εγγραφής, χρήση grep
    result=$(grep -i "$search_query" "$FILE_PATH")

    if [[ -z "$result" ]]; then
        echo "No matching passenger found."
        exit 1
    fi

    echo "Passenger found: $result"

    # Ζήτηση για το πεδίο προς αλλαγή
    echo "Enter the field to update (e.g., fullname, age, country, status, rescued, or record):"
    read field_value

    # Διαχωρισμός του πεδίου και της νέας τιμής
    IFS=":" read field new_value <<< "$field_value"

    # Ενημέρωση συγκεκριμένου πεδίου ή όλης της εγγραφής
    if [[ "$field" == "record" ]]; then
        echo "Updating entire record..."
        # Αντικατάσταση της εγγραφής με τη νέα τιμή
        sed  -i "/$search_query/c$new_value" "$FILE_PATH"
    else
        echo "Updating field '$field' to '$new_value'..."
        # Αντικατάσταση μόνο του συγκεκριμένου πεδίου
        case "$field" in
            fullname)
                column=2 ;;
            age)
                column=3 ;;
            country)
                column=4 ;;
            status)
                column=5 ;;
            rescued)
                column=6 ;;
            *)
                echo "Error: Invalid field '$field'"
                exit 1 ;;
        esac

        # Αντικατάσταση του πεδίου χρησιμοποιώντας awk με σωστό διαχωριστή
        awk -F";" -v search="$search_query" -v col="$column" -v value="$new_value" 'BEGIN {OFS=FS} 
        $0 ~ search { $col=value }1' "$FILE_PATH" > temp && mv temp "$FILE_PATH"
    fi

    # Εμφάνιση του νέου αποτελέσματος
    updated_result=$(grep -i "$search_query" "$FILE_PATH")
    echo "Passenger updated:"
    echo "Old: $result"
    echo "New: $updated_result"
}






#Function for display file
display_file() {
#Χρήση εντολής less, το περιεχόμενο του αρχείου εμφανίζεται ανά μια σελίδα τη φορά
    # Ορισμός διαδρομής αρχείου
    FILE_PATH="./passenger_data.csv"

    # Έλεγχος αν υπάρχει το αρχείο
    if [[ ! -f "$FILE_PATH" ]]; then
        echo "Error: $FILE_PATH does not exist."
        exit 1
    fi
    
 while true; do
    echo "Press Enter to start viewing the file with less or q  to exit."
    # Αναμονή για είσοδο από τον χρήστη
        if ! read -n1 -s key; then
            
            exit 0
        fi

        # Αν πατηθεί Enter, ξεκινά το less
        if [[ -z "$key" ]]; then
            break
        fi
    done

    # Προβολή του αρχείου με less
    less "$FILE_PATH"
}

#Functio for generate reports
generate_reports() {
    
    FILE_PATH="./passenger_data.csv"

    if [[ ! -f "$FILE_PATH" ]]; then
        echo "Error: $FILE_PATH does not exist."
        exit 1
    fi

    # 1. Εύρεση και προβολή ηλικιακών ομάδων, χρήση awk
    echo "Generating age groups report..."
    awk -F";" '
    {
        if ($3 >= 0 && $3 <= 18) age_group["0-18"]++
        else if ($3 >= 19 && $3 <= 35) age_group["19-35"]++
        else if ($3 >= 36 && $3 <= 50) age_group["36-50"]++
        else if ($3 >= 51) age_group["51+"]++
    }
    END {
        for (group in age_group)
            print group, age_group[group]
    }' "$FILE_PATH" > ages.txt

    # 2. Υπολογισμός ποσοστού συμμετεχόντων στη διάσωση για κάθε ηλικιακή ομάδα
    echo "Calculating rescue percentages by age group..."
    awk -F";" '
    {
        if ($3 >= 0 && $3 <= 18) {
            age_group["0-18"]++
            if (tolower($6) == "yes") rescued["0-18"]++
        } else if ($3 >= 19 && $3 <= 35) {
            age_group["19-35"]++
            if (tolower($6) == "yes") rescued["19-35"]++
        } else if ($3 >= 36 && $3 <= 50) {
            age_group["36-50"]++
            if (tolower($6) == "yes") rescued["36-50"]++
        } else if ($3 >= 51) {
            age_group["51+"]++
            if (tolower($6) == "yes") rescued["51+"]++
        }
    }
#Format String .
    END {
        for (group in age_group)
            printf "%s %.2f%%\n", group, (rescued[group] / age_group[group]) * 100
    }' "$FILE_PATH" > percentages.txt

    # 3. Υπολογισμός μέσης ηλικίας ανά κατηγορία επιβατών (Passenger/Crew)
    echo "Calculating average age by category..."
    awk -F";" '
    {
        if ($5 == "Passenger") {
            total_passenger += $3
            count_passenger++
        } else if ($5 == "Crew") {
            total_crew += $3
            count_crew++
        }
    }
    END {
        if (count_passenger > 0)
            print "Passenger", total_passenger / count_passenger
        if (count_crew > 0)
            print "Crew", total_crew / count_crew
    }' "$FILE_PATH" > avg.txt

    # 4. Φιλτράρισμα διασωθέντων σε νέο αρχείο, χρήση grep
    echo "Filtering rescued passengers..."
   grep -i  ";yes$" "$FILE_PATH" > rescued.txt

    echo "Reports generated successfully:"
    echo "- Age groups: ages.txt"
    echo "- Rescue percentages: percentages.txt"
    echo "- Average age by category: avg.txt"
    echo "- Rescued passengers: rescued.txt"
}





# Function for displaying usage
display_usage() {
    echo "Usage: $0 [--insert | --search | --update | --help]"
    echo "  --insert     Insert data into passengers.csv"
    echo "  --search     Search for a passenger in passengers.csv"
    echo "  --update     Update passenger data in passengers.csv" 
    echo " --display      Display passenger data "
    echo " --reports     Generate reports" 
     echo "  --help       Display this help message"
    exit 1
}

# Main script logic with flags
if [[ $# -eq 0 ]]; then
    display_usage
fi


case "$1" in
    --insert)
        insert_data
        ;;
    --search)
        search_passenger
        ;;

    --update)
        update_passenger
       ;;
     --display)
          display_file
       ;;
     --reports)
        generate_reports
        ;;
    --help)
        display_usage
        ;;
    *)
        echo "Error: Invalid flag"
        display_usage
        ;;
esac


