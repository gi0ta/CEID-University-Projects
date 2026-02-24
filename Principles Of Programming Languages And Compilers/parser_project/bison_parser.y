%require "3.5.1"

%define parse.error verbose

%{
    #include <iostream>
    #include <string>
    #include "../parser_helper.h"

    extern int yylex();
    extern int yyerror(const char *s);
    extern int yylineno;

    auto parser_helper = new ParserHelper();

    DataType temp_data_type;
    bool temp_access_modifier;
%}

%union {
    int int_val;
    double double_val;
    bool bool_val;
    char char_val;
    char* str_val;
}

/*********************************
 ** Tokens
 *********************************/
%token <int_val>        INT_LITERAL;
%token <double_val>     DOUBLE_LITERAL;
%token <char_val>       CHAR_LITERAL;
%token <str_val>        IDENTIFIER CLASS_NAME STRING_LITERAL;

%token  EQ NEQ AND OR;

%token  PRINT OUT DOUBLE_SP

%token  INT CHAR DOUBLE BOOLEAN
        STRING CLASS NEW RETURN
        VOID IF ELSE WHILE DO FOR
        SWITCH CASE DEFAULT BREAK
        TRUE FALSE PUBLIC PRIVATE;


/*********************************
 ** Non-Terminal types
 *********************************/

%nterm <double_val>     double_literal;
%nterm <bool_val>       bool_literal access_modifier;
%nterm <str_val>        identifier begin_class_declaration;

// Ο πρώτος κανόνας ορίζει τι πρεπει να δεχεται ο parser
%start program

%left '+' '-'
%left '*' '/'
%left '>' '<' EQ NEQ
%left AND OR
%left '.'

%%

// Το προγραμμα αποτελείται απο μια η περισσότερες classes και επιπλέον κώδικα
program:
        class_declaration code
    ;

code:
        class_declaration code
    |   method_declaration code
    |   variable_declaration code
    |   print_command code
    |   %empty


// ------------------------------- Declarations -------------------------------

class_declaration:
        begin_class_declaration '{' class_body '}'
            {
                if ( !parser_helper->in_class.empty() ) {
                    parser_helper->in_class.pop_back();
                }
            }
    ;

begin_class_declaration:
        access_modifier CLASS CLASS_NAME
            {
                parser_helper->addClass($3, $1);
                parser_helper->in_class.push_back($3);
            }
    ;

class_body:
        variable_declaration class_body
    |   method_declaration class_body
    |   class_declaration class_body
    |   object_creation class_body
    |   %empty
    ;

class_member:
        CLASS_NAME'.'identifier
            {
                const auto opt_error = parser_helper->getVariable($1, $3);
                if(opt_error){
                    yyerror(opt_error.value().c_str());
                    YYERROR;
                }
            }
    |   CLASS_NAME'.'identifier'(' method_call_parameter_list ')'
            {
                const auto opt_error = parser_helper->getMethod($1, $3);
                if(opt_error){
                    yyerror(opt_error.value().c_str());
                    YYERROR;
                }
            }
    ;


// -------------- Ερώτημα 1 --------------
// variable_declaration:
//        access_modifier data_type identifier ';'
//        {
//            parser_helper->addVariable($3, temp_data_type, $1);
//        }
//    ;

// -------------- Ερώτημα 2 --------------

variable_declaration:
        access_modifier data_type identifier opt_more_variables ';'
        {
            parser_helper->addVariable($3, temp_data_type, $1);
        }
    |   variable_declaration_with_assign opt_more_variables_with_assign ';'
    ;

variable_declaration_with_assign:
        access_modifier data_type identifier '=' expression
            {
                parser_helper->addVariable($3, temp_data_type, $1);
            }
    ;

opt_more_variables:
        ',' identifier opt_more_variables   { parser_helper->addVariable($2, temp_data_type, temp_access_modifier); }
    |   %empty
    ;

opt_more_variables_with_assign:
        ',' identifier '=' expression opt_more_variables_with_assign    { parser_helper->addVariable($2, temp_data_type, temp_access_modifier); }
    |   %empty
    ;

// -------------- Τέλος Ερώτημα 2 --------------

opt_variable_declarations:
        opt_variable_declarations variable_declaration
    |   %empty
    ;

method_declaration:
        access_modifier data_type identifier '(' method_parameter_list ')' '{' method_body '}'
            {
                parser_helper->addMethod($3, temp_data_type ,$1);
            }
    |   access_modifier VOID identifier '(' method_parameter_list ')' '{' method_body '}'
            {
                parser_helper->addMethod($3, DataType::VOID_TYPE,$1);
            }
    ;

method_parameter_list:
        method_parameter_list ',' method_parameter
    |   method_parameter
    |   %empty
    ;

method_call_parameter_list:
        method_call_parameter_list ',' method_call_parameter
    |   method_call_parameter
    |   %empty
    ;

method_parameter:
        data_type identifier
    ;

method_call_parameter:
        identifier
    ;

method_body:
        opt_variable_declarations commands opt_return
    ;


// ------------------------------- Commands -------------------------------

commands:
        command commands
    |   %empty
    ;

commands_with_break:
        commands
    |   commands BREAK ';'
    ;

command:
        print_command
    |   assign_command
    |   object_creation
    |   if_statement
    |   switch_statement
    |   do_loop
    |   for_loop
    ;

object_creation:
        CLASS_NAME identifier '=' object_instation ';'
    ;

print_command:
        PRINT'.'OUT '(' STRING_LITERAL ')' ';'
    |   PRINT'.'OUT '(' STRING_LITERAL ',' method_call_parameter_list')' ';'
    ;

assign_command:
        identifier '=' expression ';'
        {
            const auto opt_error = parser_helper->getVariable(parser_helper->in_class.back(), $1);
            if(opt_error){
                yyerror(opt_error.value().c_str());
                YYERROR;
            }
        }
    |   class_member '=' expression ';'
    ;

assign_command_no_semicolon:
        identifier '=' expression
    |   class_member '=' expression
    ;

// ------------------------------- If & Switch -------------------------------

if_statement:
        IF '(' bool_expression ')' '{' commands_with_break '}' else_if_list else_statement
    ;

else_if_statement:
        ELSE IF '(' bool_expression ')' '{' commands_with_break '}'
    ;

else_if_list:
        else_if_list else_if_statement
    |   %empty
    ;

else_statement:
        ELSE '{' commands_with_break '}'
    |   %empty
    ;

switch_statement:
        SWITCH '(' expression ')' '{' case_statement_list opt_default '}'

case_statement:
        CASE expression ':' commands

case_statement_list:
        case_statement_list case_statement
    |   case_statement
    ;

opt_default:
        DEFAULT ':' commands
    |   %empty
    ;

// ------------------------------- Loops -------------------------------

do_loop:
        DO '{' commands_with_break '}' WHILE '(' bool_expression ')'
    ;

for_loop:
        FOR '(' variable_declaration_with_assign ';' comparison_expression ';' assign_command_no_semicolon ')' '{' commands_with_break '}'


// ------------------------------- Expressions -------------------------------

expression:
        numerical_expression
    |   bool_expression
    |   CHAR_LITERAL
    |   STRING_LITERAL
    |   object_instation
    |   identifier '(' method_call_parameter_list ')'  // Κλήση μεθόδου
            {
                const auto opt_error = parser_helper->getMethod(parser_helper->in_class.back(), $1);
                if(opt_error){
                    yyerror(opt_error.value().c_str());
                    YYERROR;
                }
            }
    ;


object_instation:
        NEW CLASS_NAME '(' ')'  // Δημηουργία αντικειμένου κλάσης
            {
                const auto opt_error = parser_helper->getClass($2);
                if(opt_error){
                    yyerror(opt_error.value().c_str());
                    YYERROR;
                }
            }
    ;

numerical_expression:
        INT_LITERAL
    |   double_literal
    |   identifier
    |   class_member
    |   '(' numerical_expression ')'
    |   '-' numerical_expression
    |   numerical_expression '+' numerical_expression
    |   numerical_expression '-' numerical_expression
    |   numerical_expression '*' numerical_expression
    |   numerical_expression '/' numerical_expression
    ;

bool_expression:
        bool_literal
    |   comparison_expression
    |   '(' bool_expression ')'
    |   bool_expression AND bool_expression
    |   bool_expression OR bool_expression
    ;

comparison_expression:
        '(' comparison_expression ')'
    |   numerical_expression '>' numerical_expression
    |   numerical_expression '<' numerical_expression
    |   numerical_expression EQ numerical_expression
    |   numerical_expression NEQ numerical_expression
    ;

bool_literal:
        TRUE    { $$ = true; }
    |   FALSE    { $$ = false; }
    ;

double_literal:
        DOUBLE_LITERAL DOUBLE_SP  { $$ = $1; }
    ;


// ------------------------------- Misc -------------------------------
opt_return:
        RETURN expression ';'
    |   RETURN ';'
    |   %empty
    ;

identifier:
        CLASS_NAME  { $$ = $1; }
    |   IDENTIFIER  { $$ = $1; }
    ;

data_type:
        INT     { temp_data_type = DataType::INT_TYPE; }
    |   DOUBLE  { temp_data_type = DataType::DOUBLE_TYPE; }
    |   BOOLEAN { temp_data_type = DataType::BOOL_TYPE; }
    |   CHAR    { temp_data_type = DataType::CHAR_TYPE; }
    |   STRING  { temp_data_type = DataType::STRING_TYPE; }
    ;

access_modifier:
        PUBLIC      { $$ = true; temp_access_modifier = true; }
    |   PRIVATE     { $$ = false; temp_access_modifier = false; }
    |   %empty      { $$ = true; temp_access_modifier = true; }
    ;

%%

int yyerror(const char *s) {
    std::cerr << "Error at line " << yylineno << " : " << s << std::endl;
    return 0;
}
