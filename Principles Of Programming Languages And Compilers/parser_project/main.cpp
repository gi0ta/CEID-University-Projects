#include <iostream>
#include <fstream>
#include "generated/bison_parser.tab.hpp"

extern FILE* yyin;
extern int yyparse();

int main(int argc, char* argv[]) {
  if (argc != 2) {
    std::cerr << "Usage: " << argv[0] << " <filename>" << std::endl;
    return 1;
  }

  const char* filename = argv[1];
  FILE* file = fopen(filename, "r");
  if (!file) {
    std::cerr << "Error: Unable to open file '" << filename << "'" << std::endl;
    return 1;
  }

  yyin = file; // Set the lexer to read from the file

  if (yyparse() == 0) {
    std::cout << "Parsing completed successfully!" << std::endl;
  } else {
    std::cerr << "Parsing failed!" << std::endl;
  }

  fclose(file); // Close the file
  return 0;
}