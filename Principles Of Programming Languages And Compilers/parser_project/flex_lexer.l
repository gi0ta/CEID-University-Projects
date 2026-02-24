%option yylineno

%{
#include <cstdlib>
#include <iostream>

#include "../parser_helper.h"
#include <string>
#include "bison_parser.tab.hpp"

%}

%%

[ \t\r\n]+            /* Αγνόησε τα κενά */;

"//".*                  { /* Αγνόησε σχόλιο μίας γραμμής */ }
"/*"([^*]|\*[^/])*"*/"  { /* Αγνόησε σχόλιο πολλαπλών γραμμών */ }

"INT"              { return INT; }
"CHAR"             { return CHAR; }
"DOUBLE"           { return DOUBLE; }
"BOOLEAN"          { return BOOLEAN; }
"STRING"           { return STRING; }
"CLASS"            { return CLASS; }
"NEW"              { return NEW; }
"RETURN"           { return RETURN; }
"VOID"             { return VOID; }
"IF"               { return IF; }
"ELSE"             { return ELSE; }
"WHILE"            { return WHILE; }
"DO"               { return DO; }
"FOR"              { return FOR; }
"SWITCH"           { return SWITCH; }
"CASE"             { return CASE; }
"DEFAULT"          { return DEFAULT; }
"BREAK"            { return BREAK; }
"TRUE"             { return TRUE; }
"FALSE"            { return FALSE; }
"PUBLIC"           { return PUBLIC; }
"PRIVATE"          { return PRIVATE; }

"PRINT"             { return PRINT; }
"OUT"               { return OUT; }
"d"                 { return DOUBLE_SP; }

"="                { return '='; }
"+"                { return '+'; }
"-"                { return '-'; }
"*"                { return '*'; }
"/"                { return '/'; }
">"                { return '>'; }
"<"                { return '<'; }
"=="               { return EQ; }
"!="               { return NEQ; }
"&&"               { return AND; }
"||"               { return OR; }
"."                { return '.'; }
"("                { return '('; }
")"                { return ')'; }
"{"                { return '{'; }
"}"                { return '}'; }
";"                { return ';'; }
","                { return ','; }
":"                { return ':'; }    // For switch/case

[0-9]+"."[0-9]*     { yylval.double_val = std::atof(yytext); return DOUBLE_LITERAL; }
[0-9]+              { yylval.int_val = std::atoi(yytext); return INT_LITERAL; }

'[^']'              { yylval.char_val = yytext[1]; return CHAR_LITERAL; }       // Χαρακτήρες
'\\[a-zA-Z]'        { yylval.char_val = yytext[2]; return CHAR_LITERAL; }       // Ειδικοί Χαρακτήρες
\"([^\\"]|\\.)*\"   { yylval.str_val = strdup(yytext); return STRING_LITERAL; } // Strings

[A-Z][a-zA-Z0-9_]*      { yylval.str_val = strdup(yytext); return CLASS_NAME; }     // Ονόματα κλάσεων (ξεκινούν με κεφαλαίο)
[a-zA-Z_][a-zA-Z0-9_]*  { yylval.str_val = strdup(yytext);  return IDENTIFIER; }    // Γενικά αναγνωριστικά


<<EOF>>            { return 0; }      // Τέλος αρχείου
.                  { std::cerr << "Unexpected character: " << yytext << std::endl; return 0; }

%%

int yywrap() {
    return 1;  // Indicate end of input to the parser
}
