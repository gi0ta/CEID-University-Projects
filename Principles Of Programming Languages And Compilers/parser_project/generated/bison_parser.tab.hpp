/* A Bison parser, made by GNU Bison 3.8.2.  */

/* Bison interface for Yacc-like parsers in C

   Copyright (C) 1984, 1989-1990, 2000-2015, 2018-2021 Free Software Foundation,
   Inc.

   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program.  If not, see <https://www.gnu.org/licenses/>.  */

/* As a special exception, you may create a larger work that contains
   part or all of the Bison parser skeleton and distribute that work
   under terms of your choice, so long as that work isn't itself a
   parser generator using the skeleton or a modified version thereof
   as a parser skeleton.  Alternatively, if you modify or redistribute
   the parser skeleton itself, you may (at your option) remove this
   special exception, which will cause the skeleton and the resulting
   Bison output files to be licensed under the GNU General Public
   License without this special exception.

   This special exception was added by the Free Software Foundation in
   version 2.2 of Bison.  */

/* DO NOT RELY ON FEATURES THAT ARE NOT DOCUMENTED in the manual,
   especially those whose name start with YY_ or yy_.  They are
   private implementation details that can be changed or removed.  */

#ifndef YY_YY_HOME_VAGELIS_DESKTOP_PARSER_PROJECT_GENERATED_BISON_PARSER_TAB_HPP_INCLUDED
# define YY_YY_HOME_VAGELIS_DESKTOP_PARSER_PROJECT_GENERATED_BISON_PARSER_TAB_HPP_INCLUDED
/* Debug traces.  */
#ifndef YYDEBUG
# define YYDEBUG 0
#endif
#if YYDEBUG
extern int yydebug;
#endif

/* Token kinds.  */
#ifndef YYTOKENTYPE
# define YYTOKENTYPE
  enum yytokentype
  {
    YYEMPTY = -2,
    YYEOF = 0,                     /* "end of file"  */
    YYerror = 256,                 /* error  */
    YYUNDEF = 257,                 /* "invalid token"  */
    INT_LITERAL = 258,             /* INT_LITERAL  */
    DOUBLE_LITERAL = 259,          /* DOUBLE_LITERAL  */
    CHAR_LITERAL = 260,            /* CHAR_LITERAL  */
    IDENTIFIER = 261,              /* IDENTIFIER  */
    CLASS_NAME = 262,              /* CLASS_NAME  */
    STRING_LITERAL = 263,          /* STRING_LITERAL  */
    EQ = 264,                      /* EQ  */
    NEQ = 265,                     /* NEQ  */
    AND = 266,                     /* AND  */
    OR = 267,                      /* OR  */
    PRINT = 268,                   /* PRINT  */
    OUT = 269,                     /* OUT  */
    DOUBLE_SP = 270,               /* DOUBLE_SP  */
    INT = 271,                     /* INT  */
    CHAR = 272,                    /* CHAR  */
    DOUBLE = 273,                  /* DOUBLE  */
    BOOLEAN = 274,                 /* BOOLEAN  */
    STRING = 275,                  /* STRING  */
    CLASS = 276,                   /* CLASS  */
    NEW = 277,                     /* NEW  */
    RETURN = 278,                  /* RETURN  */
    VOID = 279,                    /* VOID  */
    IF = 280,                      /* IF  */
    ELSE = 281,                    /* ELSE  */
    WHILE = 282,                   /* WHILE  */
    DO = 283,                      /* DO  */
    FOR = 284,                     /* FOR  */
    SWITCH = 285,                  /* SWITCH  */
    CASE = 286,                    /* CASE  */
    DEFAULT = 287,                 /* DEFAULT  */
    BREAK = 288,                   /* BREAK  */
    TRUE = 289,                    /* TRUE  */
    FALSE = 290,                   /* FALSE  */
    PUBLIC = 291,                  /* PUBLIC  */
    PRIVATE = 292                  /* PRIVATE  */
  };
  typedef enum yytokentype yytoken_kind_t;
#endif

/* Value type.  */
#if ! defined YYSTYPE && ! defined YYSTYPE_IS_DECLARED
union YYSTYPE
{
#line 20 "bison_parser.y"

    int int_val;
    double double_val;
    bool bool_val;
    char char_val;
    char* str_val;

#line 109 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.hpp"

};
typedef union YYSTYPE YYSTYPE;
# define YYSTYPE_IS_TRIVIAL 1
# define YYSTYPE_IS_DECLARED 1
#endif


extern YYSTYPE yylval;


int yyparse (void);


#endif /* !YY_YY_HOME_VAGELIS_DESKTOP_PARSER_PROJECT_GENERATED_BISON_PARSER_TAB_HPP_INCLUDED  */
