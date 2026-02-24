import pandas as pd

data = {
    "code": [9005, 9006, 9007],
    "fullname": ["Rosie Rosie", "Reb Kou", "Emily Brown"],
    "age": [30, 25, 22],
    "country": ["USA", "Japan", "UK"],
    "status": ["Passenger", "Crew", "Passenger"],
    "rescued": ["Yes", "No", "Yes"]
}


df = pd.DataFrame(data)


file_path = file_path = "/home/gg/Documents/shell_workspace/passengers_data_example.csv"

df.to_csv(file_path, index=False, sep=",")

