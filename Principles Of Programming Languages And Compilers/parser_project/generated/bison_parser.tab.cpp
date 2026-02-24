/* A Bison parser, made by GNU Bison 3.8.2.  */

/* Bison implementation for Yacc-like parsers in C

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

/* C LALR(1) parser skeleton written by Richard Stallman, by
   simplifying the original so-called "semantic" parser.  */

/* DO NOT RELY ON FEATURES THAT ARE NOT DOCUMENTED in the manual,
   especially those whose name start with YY_ or yy_.  They are
   private implementation details that can be changed or removed.  */

/* All symbols defined below should begin with yy or YY, to avoid
   infringing on user name space.  This should be done even for local
   variables, as they might otherwise be expanded by user macros.
   There are some unavoidable exceptions within include files to
   define necessary library symbols; they are noted "INFRINGES ON
   USER NAME SPACE" below.  */

/* Identify Bison output, and Bison version.  */
#define YYBISON 30802

/* Bison version string.  */
#define YYBISON_VERSION "3.8.2"

/* Skeleton name.  */
#define YYSKELETON_NAME "yacc.c"

/* Pure parsers.  */
#define YYPURE 0

/* Push parsers.  */
#define YYPUSH 0

/* Pull parsers.  */
#define YYPULL 1




/* First part of user prologue.  */
#line 5 "bison_parser.y"

    #include <iostream>
    #include <string>
    #include "../parser_helper.h"

    extern int yylex();
    extern int yyerror(const char *s);
    extern int yylineno;

    auto parser_helper = new ParserHelper();

    DataType temp_data_type;
    bool temp_access_modifier;

#line 86 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"

# ifndef YY_CAST
#  ifdef __cplusplus
#   define YY_CAST(Type, Val) static_cast<Type> (Val)
#   define YY_REINTERPRET_CAST(Type, Val) reinterpret_cast<Type> (Val)
#  else
#   define YY_CAST(Type, Val) ((Type) (Val))
#   define YY_REINTERPRET_CAST(Type, Val) ((Type) (Val))
#  endif
# endif
# ifndef YY_NULLPTR
#  if defined __cplusplus
#   if 201103L <= __cplusplus
#    define YY_NULLPTR nullptr
#   else
#    define YY_NULLPTR 0
#   endif
#  else
#   define YY_NULLPTR ((void*)0)
#  endif
# endif

#include "bison_parser.tab.hpp"
/* Symbol kind.  */
enum yysymbol_kind_t
{
  YYSYMBOL_YYEMPTY = -2,
  YYSYMBOL_YYEOF = 0,                      /* "end of file"  */
  YYSYMBOL_YYerror = 1,                    /* error  */
  YYSYMBOL_YYUNDEF = 2,                    /* "invalid token"  */
  YYSYMBOL_INT_LITERAL = 3,                /* INT_LITERAL  */
  YYSYMBOL_DOUBLE_LITERAL = 4,             /* DOUBLE_LITERAL  */
  YYSYMBOL_CHAR_LITERAL = 5,               /* CHAR_LITERAL  */
  YYSYMBOL_IDENTIFIER = 6,                 /* IDENTIFIER  */
  YYSYMBOL_CLASS_NAME = 7,                 /* CLASS_NAME  */
  YYSYMBOL_STRING_LITERAL = 8,             /* STRING_LITERAL  */
  YYSYMBOL_EQ = 9,                         /* EQ  */
  YYSYMBOL_NEQ = 10,                       /* NEQ  */
  YYSYMBOL_AND = 11,                       /* AND  */
  YYSYMBOL_OR = 12,                        /* OR  */
  YYSYMBOL_PRINT = 13,                     /* PRINT  */
  YYSYMBOL_OUT = 14,                       /* OUT  */
  YYSYMBOL_DOUBLE_SP = 15,                 /* DOUBLE_SP  */
  YYSYMBOL_INT = 16,                       /* INT  */
  YYSYMBOL_CHAR = 17,                      /* CHAR  */
  YYSYMBOL_DOUBLE = 18,                    /* DOUBLE  */
  YYSYMBOL_BOOLEAN = 19,                   /* BOOLEAN  */
  YYSYMBOL_STRING = 20,                    /* STRING  */
  YYSYMBOL_CLASS = 21,                     /* CLASS  */
  YYSYMBOL_NEW = 22,                       /* NEW  */
  YYSYMBOL_RETURN = 23,                    /* RETURN  */
  YYSYMBOL_VOID = 24,                      /* VOID  */
  YYSYMBOL_IF = 25,                        /* IF  */
  YYSYMBOL_ELSE = 26,                      /* ELSE  */
  YYSYMBOL_WHILE = 27,                     /* WHILE  */
  YYSYMBOL_DO = 28,                        /* DO  */
  YYSYMBOL_FOR = 29,                       /* FOR  */
  YYSYMBOL_SWITCH = 30,                    /* SWITCH  */
  YYSYMBOL_CASE = 31,                      /* CASE  */
  YYSYMBOL_DEFAULT = 32,                   /* DEFAULT  */
  YYSYMBOL_BREAK = 33,                     /* BREAK  */
  YYSYMBOL_TRUE = 34,                      /* TRUE  */
  YYSYMBOL_FALSE = 35,                     /* FALSE  */
  YYSYMBOL_PUBLIC = 36,                    /* PUBLIC  */
  YYSYMBOL_PRIVATE = 37,                   /* PRIVATE  */
  YYSYMBOL_38_ = 38,                       /* '+'  */
  YYSYMBOL_39_ = 39,                       /* '-'  */
  YYSYMBOL_40_ = 40,                       /* '*'  */
  YYSYMBOL_41_ = 41,                       /* '/'  */
  YYSYMBOL_42_ = 42,                       /* '>'  */
  YYSYMBOL_43_ = 43,                       /* '<'  */
  YYSYMBOL_44_ = 44,                       /* '.'  */
  YYSYMBOL_45_ = 45,                       /* '{'  */
  YYSYMBOL_46_ = 46,                       /* '}'  */
  YYSYMBOL_47_ = 47,                       /* '('  */
  YYSYMBOL_48_ = 48,                       /* ')'  */
  YYSYMBOL_49_ = 49,                       /* ';'  */
  YYSYMBOL_50_ = 50,                       /* '='  */
  YYSYMBOL_51_ = 51,                       /* ','  */
  YYSYMBOL_52_ = 52,                       /* ':'  */
  YYSYMBOL_YYACCEPT = 53,                  /* $accept  */
  YYSYMBOL_program = 54,                   /* program  */
  YYSYMBOL_code = 55,                      /* code  */
  YYSYMBOL_class_declaration = 56,         /* class_declaration  */
  YYSYMBOL_begin_class_declaration = 57,   /* begin_class_declaration  */
  YYSYMBOL_class_body = 58,                /* class_body  */
  YYSYMBOL_class_member = 59,              /* class_member  */
  YYSYMBOL_variable_declaration = 60,      /* variable_declaration  */
  YYSYMBOL_variable_declaration_with_assign = 61, /* variable_declaration_with_assign  */
  YYSYMBOL_opt_more_variables = 62,        /* opt_more_variables  */
  YYSYMBOL_opt_more_variables_with_assign = 63, /* opt_more_variables_with_assign  */
  YYSYMBOL_opt_variable_declarations = 64, /* opt_variable_declarations  */
  YYSYMBOL_method_declaration = 65,        /* method_declaration  */
  YYSYMBOL_method_parameter_list = 66,     /* method_parameter_list  */
  YYSYMBOL_method_call_parameter_list = 67, /* method_call_parameter_list  */
  YYSYMBOL_method_parameter = 68,          /* method_parameter  */
  YYSYMBOL_method_call_parameter = 69,     /* method_call_parameter  */
  YYSYMBOL_method_body = 70,               /* method_body  */
  YYSYMBOL_commands = 71,                  /* commands  */
  YYSYMBOL_commands_with_break = 72,       /* commands_with_break  */
  YYSYMBOL_command = 73,                   /* command  */
  YYSYMBOL_object_creation = 74,           /* object_creation  */
  YYSYMBOL_print_command = 75,             /* print_command  */
  YYSYMBOL_assign_command = 76,            /* assign_command  */
  YYSYMBOL_assign_command_no_semicolon = 77, /* assign_command_no_semicolon  */
  YYSYMBOL_if_statement = 78,              /* if_statement  */
  YYSYMBOL_else_if_statement = 79,         /* else_if_statement  */
  YYSYMBOL_else_if_list = 80,              /* else_if_list  */
  YYSYMBOL_else_statement = 81,            /* else_statement  */
  YYSYMBOL_switch_statement = 82,          /* switch_statement  */
  YYSYMBOL_case_statement = 83,            /* case_statement  */
  YYSYMBOL_case_statement_list = 84,       /* case_statement_list  */
  YYSYMBOL_opt_default = 85,               /* opt_default  */
  YYSYMBOL_do_loop = 86,                   /* do_loop  */
  YYSYMBOL_for_loop = 87,                  /* for_loop  */
  YYSYMBOL_expression = 88,                /* expression  */
  YYSYMBOL_object_instation = 89,          /* object_instation  */
  YYSYMBOL_numerical_expression = 90,      /* numerical_expression  */
  YYSYMBOL_bool_expression = 91,           /* bool_expression  */
  YYSYMBOL_comparison_expression = 92,     /* comparison_expression  */
  YYSYMBOL_bool_literal = 93,              /* bool_literal  */
  YYSYMBOL_double_literal = 94,            /* double_literal  */
  YYSYMBOL_opt_return = 95,                /* opt_return  */
  YYSYMBOL_identifier = 96,                /* identifier  */
  YYSYMBOL_data_type = 97,                 /* data_type  */
  YYSYMBOL_access_modifier = 98            /* access_modifier  */
};
typedef enum yysymbol_kind_t yysymbol_kind_t;




#ifdef short
# undef short
#endif

/* On compilers that do not define __PTRDIFF_MAX__ etc., make sure
   <limits.h> and (if available) <stdint.h> are included
   so that the code can choose integer types of a good width.  */

#ifndef __PTRDIFF_MAX__
# include <limits.h> /* INFRINGES ON USER NAME SPACE */
# if defined __STDC_VERSION__ && 199901 <= __STDC_VERSION__
#  include <stdint.h> /* INFRINGES ON USER NAME SPACE */
#  define YY_STDINT_H
# endif
#endif

/* Narrow types that promote to a signed type and that can represent a
   signed or unsigned integer of at least N bits.  In tables they can
   save space and decrease cache pressure.  Promoting to a signed type
   helps avoid bugs in integer arithmetic.  */

#ifdef __INT_LEAST8_MAX__
typedef __INT_LEAST8_TYPE__ yytype_int8;
#elif defined YY_STDINT_H
typedef int_least8_t yytype_int8;
#else
typedef signed char yytype_int8;
#endif

#ifdef __INT_LEAST16_MAX__
typedef __INT_LEAST16_TYPE__ yytype_int16;
#elif defined YY_STDINT_H
typedef int_least16_t yytype_int16;
#else
typedef short yytype_int16;
#endif

/* Work around bug in HP-UX 11.23, which defines these macros
   incorrectly for preprocessor constants.  This workaround can likely
   be removed in 2023, as HPE has promised support for HP-UX 11.23
   (aka HP-UX 11i v2) only through the end of 2022; see Table 2 of
   <https://h20195.www2.hpe.com/V2/getpdf.aspx/4AA4-7673ENW.pdf>.  */
#ifdef __hpux
# undef UINT_LEAST8_MAX
# undef UINT_LEAST16_MAX
# define UINT_LEAST8_MAX 255
# define UINT_LEAST16_MAX 65535
#endif

#if defined __UINT_LEAST8_MAX__ && __UINT_LEAST8_MAX__ <= __INT_MAX__
typedef __UINT_LEAST8_TYPE__ yytype_uint8;
#elif (!defined __UINT_LEAST8_MAX__ && defined YY_STDINT_H \
       && UINT_LEAST8_MAX <= INT_MAX)
typedef uint_least8_t yytype_uint8;
#elif !defined __UINT_LEAST8_MAX__ && UCHAR_MAX <= INT_MAX
typedef unsigned char yytype_uint8;
#else
typedef short yytype_uint8;
#endif

#if defined __UINT_LEAST16_MAX__ && __UINT_LEAST16_MAX__ <= __INT_MAX__
typedef __UINT_LEAST16_TYPE__ yytype_uint16;
#elif (!defined __UINT_LEAST16_MAX__ && defined YY_STDINT_H \
       && UINT_LEAST16_MAX <= INT_MAX)
typedef uint_least16_t yytype_uint16;
#elif !defined __UINT_LEAST16_MAX__ && USHRT_MAX <= INT_MAX
typedef unsigned short yytype_uint16;
#else
typedef int yytype_uint16;
#endif

#ifndef YYPTRDIFF_T
# if defined __PTRDIFF_TYPE__ && defined __PTRDIFF_MAX__
#  define YYPTRDIFF_T __PTRDIFF_TYPE__
#  define YYPTRDIFF_MAXIMUM __PTRDIFF_MAX__
# elif defined PTRDIFF_MAX
#  ifndef ptrdiff_t
#   include <stddef.h> /* INFRINGES ON USER NAME SPACE */
#  endif
#  define YYPTRDIFF_T ptrdiff_t
#  define YYPTRDIFF_MAXIMUM PTRDIFF_MAX
# else
#  define YYPTRDIFF_T long
#  define YYPTRDIFF_MAXIMUM LONG_MAX
# endif
#endif

#ifndef YYSIZE_T
# ifdef __SIZE_TYPE__
#  define YYSIZE_T __SIZE_TYPE__
# elif defined size_t
#  define YYSIZE_T size_t
# elif defined __STDC_VERSION__ && 199901 <= __STDC_VERSION__
#  include <stddef.h> /* INFRINGES ON USER NAME SPACE */
#  define YYSIZE_T size_t
# else
#  define YYSIZE_T unsigned
# endif
#endif

#define YYSIZE_MAXIMUM                                  \
  YY_CAST (YYPTRDIFF_T,                                 \
           (YYPTRDIFF_MAXIMUM < YY_CAST (YYSIZE_T, -1)  \
            ? YYPTRDIFF_MAXIMUM                         \
            : YY_CAST (YYSIZE_T, -1)))

#define YYSIZEOF(X) YY_CAST (YYPTRDIFF_T, sizeof (X))


/* Stored state numbers (used for stacks). */
typedef yytype_uint8 yy_state_t;

/* State numbers in computations.  */
typedef int yy_state_fast_t;

#ifndef YY_
# if defined YYENABLE_NLS && YYENABLE_NLS
#  if ENABLE_NLS
#   include <libintl.h> /* INFRINGES ON USER NAME SPACE */
#   define YY_(Msgid) dgettext ("bison-runtime", Msgid)
#  endif
# endif
# ifndef YY_
#  define YY_(Msgid) Msgid
# endif
#endif


#ifndef YY_ATTRIBUTE_PURE
# if defined __GNUC__ && 2 < __GNUC__ + (96 <= __GNUC_MINOR__)
#  define YY_ATTRIBUTE_PURE __attribute__ ((__pure__))
# else
#  define YY_ATTRIBUTE_PURE
# endif
#endif

#ifndef YY_ATTRIBUTE_UNUSED
# if defined __GNUC__ && 2 < __GNUC__ + (7 <= __GNUC_MINOR__)
#  define YY_ATTRIBUTE_UNUSED __attribute__ ((__unused__))
# else
#  define YY_ATTRIBUTE_UNUSED
# endif
#endif

/* Suppress unused-variable warnings by "using" E.  */
#if ! defined lint || defined __GNUC__
# define YY_USE(E) ((void) (E))
#else
# define YY_USE(E) /* empty */
#endif

/* Suppress an incorrect diagnostic about yylval being uninitialized.  */
#if defined __GNUC__ && ! defined __ICC && 406 <= __GNUC__ * 100 + __GNUC_MINOR__
# if __GNUC__ * 100 + __GNUC_MINOR__ < 407
#  define YY_IGNORE_MAYBE_UNINITIALIZED_BEGIN                           \
    _Pragma ("GCC diagnostic push")                                     \
    _Pragma ("GCC diagnostic ignored \"-Wuninitialized\"")
# else
#  define YY_IGNORE_MAYBE_UNINITIALIZED_BEGIN                           \
    _Pragma ("GCC diagnostic push")                                     \
    _Pragma ("GCC diagnostic ignored \"-Wuninitialized\"")              \
    _Pragma ("GCC diagnostic ignored \"-Wmaybe-uninitialized\"")
# endif
# define YY_IGNORE_MAYBE_UNINITIALIZED_END      \
    _Pragma ("GCC diagnostic pop")
#else
# define YY_INITIAL_VALUE(Value) Value
#endif
#ifndef YY_IGNORE_MAYBE_UNINITIALIZED_BEGIN
# define YY_IGNORE_MAYBE_UNINITIALIZED_BEGIN
# define YY_IGNORE_MAYBE_UNINITIALIZED_END
#endif
#ifndef YY_INITIAL_VALUE
# define YY_INITIAL_VALUE(Value) /* Nothing. */
#endif

#if defined __cplusplus && defined __GNUC__ && ! defined __ICC && 6 <= __GNUC__
# define YY_IGNORE_USELESS_CAST_BEGIN                          \
    _Pragma ("GCC diagnostic push")                            \
    _Pragma ("GCC diagnostic ignored \"-Wuseless-cast\"")
# define YY_IGNORE_USELESS_CAST_END            \
    _Pragma ("GCC diagnostic pop")
#endif
#ifndef YY_IGNORE_USELESS_CAST_BEGIN
# define YY_IGNORE_USELESS_CAST_BEGIN
# define YY_IGNORE_USELESS_CAST_END
#endif


#define YY_ASSERT(E) ((void) (0 && (E)))

#if 1

/* The parser invokes alloca or malloc; define the necessary symbols.  */

# ifdef YYSTACK_USE_ALLOCA
#  if YYSTACK_USE_ALLOCA
#   ifdef __GNUC__
#    define YYSTACK_ALLOC __builtin_alloca
#   elif defined __BUILTIN_VA_ARG_INCR
#    include <alloca.h> /* INFRINGES ON USER NAME SPACE */
#   elif defined _AIX
#    define YYSTACK_ALLOC __alloca
#   elif defined _MSC_VER
#    include <malloc.h> /* INFRINGES ON USER NAME SPACE */
#    define alloca _alloca
#   else
#    define YYSTACK_ALLOC alloca
#    if ! defined _ALLOCA_H && ! defined EXIT_SUCCESS
#     include <stdlib.h> /* INFRINGES ON USER NAME SPACE */
      /* Use EXIT_SUCCESS as a witness for stdlib.h.  */
#     ifndef EXIT_SUCCESS
#      define EXIT_SUCCESS 0
#     endif
#    endif
#   endif
#  endif
# endif

# ifdef YYSTACK_ALLOC
   /* Pacify GCC's 'empty if-body' warning.  */
#  define YYSTACK_FREE(Ptr) do { /* empty */; } while (0)
#  ifndef YYSTACK_ALLOC_MAXIMUM
    /* The OS might guarantee only one guard page at the bottom of the stack,
       and a page size can be as small as 4096 bytes.  So we cannot safely
       invoke alloca (N) if N exceeds 4096.  Use a slightly smaller number
       to allow for a few compiler-allocated temporary stack slots.  */
#   define YYSTACK_ALLOC_MAXIMUM 4032 /* reasonable circa 2006 */
#  endif
# else
#  define YYSTACK_ALLOC YYMALLOC
#  define YYSTACK_FREE YYFREE
#  ifndef YYSTACK_ALLOC_MAXIMUM
#   define YYSTACK_ALLOC_MAXIMUM YYSIZE_MAXIMUM
#  endif
#  if (defined __cplusplus && ! defined EXIT_SUCCESS \
       && ! ((defined YYMALLOC || defined malloc) \
             && (defined YYFREE || defined free)))
#   include <stdlib.h> /* INFRINGES ON USER NAME SPACE */
#   ifndef EXIT_SUCCESS
#    define EXIT_SUCCESS 0
#   endif
#  endif
#  ifndef YYMALLOC
#   define YYMALLOC malloc
#   if ! defined malloc && ! defined EXIT_SUCCESS
void *malloc (YYSIZE_T); /* INFRINGES ON USER NAME SPACE */
#   endif
#  endif
#  ifndef YYFREE
#   define YYFREE free
#   if ! defined free && ! defined EXIT_SUCCESS
void free (void *); /* INFRINGES ON USER NAME SPACE */
#   endif
#  endif
# endif
#endif /* 1 */

#if (! defined yyoverflow \
     && (! defined __cplusplus \
         || (defined YYSTYPE_IS_TRIVIAL && YYSTYPE_IS_TRIVIAL)))

/* A type that is properly aligned for any stack member.  */
union yyalloc
{
  yy_state_t yyss_alloc;
  YYSTYPE yyvs_alloc;
};

/* The size of the maximum gap between one aligned stack and the next.  */
# define YYSTACK_GAP_MAXIMUM (YYSIZEOF (union yyalloc) - 1)

/* The size of an array large to enough to hold all stacks, each with
   N elements.  */
# define YYSTACK_BYTES(N) \
     ((N) * (YYSIZEOF (yy_state_t) + YYSIZEOF (YYSTYPE)) \
      + YYSTACK_GAP_MAXIMUM)

# define YYCOPY_NEEDED 1

/* Relocate STACK from its old location to the new one.  The
   local variables YYSIZE and YYSTACKSIZE give the old and new number of
   elements in the stack, and YYPTR gives the new location of the
   stack.  Advance YYPTR to a properly aligned location for the next
   stack.  */
# define YYSTACK_RELOCATE(Stack_alloc, Stack)                           \
    do                                                                  \
      {                                                                 \
        YYPTRDIFF_T yynewbytes;                                         \
        YYCOPY (&yyptr->Stack_alloc, Stack, yysize);                    \
        Stack = &yyptr->Stack_alloc;                                    \
        yynewbytes = yystacksize * YYSIZEOF (*Stack) + YYSTACK_GAP_MAXIMUM; \
        yyptr += yynewbytes / YYSIZEOF (*yyptr);                        \
      }                                                                 \
    while (0)

#endif

#if defined YYCOPY_NEEDED && YYCOPY_NEEDED
/* Copy COUNT objects from SRC to DST.  The source and destination do
   not overlap.  */
# ifndef YYCOPY
#  if defined __GNUC__ && 1 < __GNUC__
#   define YYCOPY(Dst, Src, Count) \
      __builtin_memcpy (Dst, Src, YY_CAST (YYSIZE_T, (Count)) * sizeof (*(Src)))
#  else
#   define YYCOPY(Dst, Src, Count)              \
      do                                        \
        {                                       \
          YYPTRDIFF_T yyi;                      \
          for (yyi = 0; yyi < (Count); yyi++)   \
            (Dst)[yyi] = (Src)[yyi];            \
        }                                       \
      while (0)
#  endif
# endif
#endif /* !YYCOPY_NEEDED */

/* YYFINAL -- State number of the termination state.  */
#define YYFINAL  7
/* YYLAST -- Last index in YYTABLE.  */
#define YYLAST   366

/* YYNTOKENS -- Number of terminals.  */
#define YYNTOKENS  53
/* YYNNTS -- Number of nonterminals.  */
#define YYNNTS  46
/* YYNRULES -- Number of rules.  */
#define YYNRULES  111
/* YYNSTATES -- Number of states.  */
#define YYNSTATES  255

/* YYMAXUTOK -- Last valid token kind.  */
#define YYMAXUTOK   292


/* YYTRANSLATE(TOKEN-NUM) -- Symbol number corresponding to TOKEN-NUM
   as returned by yylex, with out-of-bounds checking.  */
#define YYTRANSLATE(YYX)                                \
  (0 <= (YYX) && (YYX) <= YYMAXUTOK                     \
   ? YY_CAST (yysymbol_kind_t, yytranslate[YYX])        \
   : YYSYMBOL_YYUNDEF)

/* YYTRANSLATE[TOKEN-NUM] -- Symbol number corresponding to TOKEN-NUM
   as returned by yylex.  */
static const yytype_int8 yytranslate[] =
{
       0,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
      47,    48,    40,    38,    51,    39,    44,    41,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,    52,    49,
      43,    50,    42,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,    45,     2,    46,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     2,     2,     2,     2,
       2,     2,     2,     2,     2,     2,     1,     2,     3,     4,
       5,     6,     7,     8,     9,    10,    11,    12,    13,    14,
      15,    16,    17,    18,    19,    20,    21,    22,    23,    24,
      25,    26,    27,    28,    29,    30,    31,    32,    33,    34,
      35,    36,    37
};

#if YYDEBUG
/* YYRLINE[YYN] -- Source line where rule number YYN was defined.  */
static const yytype_int16 yyrline[] =
{
       0,    68,    68,    72,    73,    74,    75,    76,    82,    91,
      99,   100,   101,   102,   103,   107,   115,   137,   141,   145,
     152,   153,   157,   158,   164,   165,   169,   173,   180,   181,
     182,   186,   187,   188,   192,   196,   200,   207,   208,   212,
     213,   217,   218,   219,   220,   221,   222,   223,   227,   231,
     232,   236,   244,   248,   249,   255,   259,   263,   264,   268,
     269,   273,   276,   279,   280,   284,   285,   291,   295,   301,
     302,   303,   304,   305,   306,   318,   329,   330,   331,   332,
     333,   334,   335,   336,   337,   338,   342,   343,   344,   345,
     346,   350,   351,   352,   353,   354,   358,   359,   363,   369,
     370,   371,   375,   376,   380,   381,   382,   383,   384,   388,
     389,   390
};
#endif

/** Accessing symbol of state STATE.  */
#define YY_ACCESSING_SYMBOL(State) YY_CAST (yysymbol_kind_t, yystos[State])

#if 1
/* The user-facing name of the symbol whose (internal) number is
   YYSYMBOL.  No bounds checking.  */
static const char *yysymbol_name (yysymbol_kind_t yysymbol) YY_ATTRIBUTE_UNUSED;

/* YYTNAME[SYMBOL-NUM] -- String name of the symbol SYMBOL-NUM.
   First, the terminals, then, starting at YYNTOKENS, nonterminals.  */
static const char *const yytname[] =
{
  "\"end of file\"", "error", "\"invalid token\"", "INT_LITERAL",
  "DOUBLE_LITERAL", "CHAR_LITERAL", "IDENTIFIER", "CLASS_NAME",
  "STRING_LITERAL", "EQ", "NEQ", "AND", "OR", "PRINT", "OUT", "DOUBLE_SP",
  "INT", "CHAR", "DOUBLE", "BOOLEAN", "STRING", "CLASS", "NEW", "RETURN",
  "VOID", "IF", "ELSE", "WHILE", "DO", "FOR", "SWITCH", "CASE", "DEFAULT",
  "BREAK", "TRUE", "FALSE", "PUBLIC", "PRIVATE", "'+'", "'-'", "'*'",
  "'/'", "'>'", "'<'", "'.'", "'{'", "'}'", "'('", "')'", "';'", "'='",
  "','", "':'", "$accept", "program", "code", "class_declaration",
  "begin_class_declaration", "class_body", "class_member",
  "variable_declaration", "variable_declaration_with_assign",
  "opt_more_variables", "opt_more_variables_with_assign",
  "opt_variable_declarations", "method_declaration",
  "method_parameter_list", "method_call_parameter_list",
  "method_parameter", "method_call_parameter", "method_body", "commands",
  "commands_with_break", "command", "object_creation", "print_command",
  "assign_command", "assign_command_no_semicolon", "if_statement",
  "else_if_statement", "else_if_list", "else_statement",
  "switch_statement", "case_statement", "case_statement_list",
  "opt_default", "do_loop", "for_loop", "expression", "object_instation",
  "numerical_expression", "bool_expression", "comparison_expression",
  "bool_literal", "double_literal", "opt_return", "identifier",
  "data_type", "access_modifier", YY_NULLPTR
};

static const char *
yysymbol_name (yysymbol_kind_t yysymbol)
{
  return yytname[yysymbol];
}
#endif

#define YYPACT_NINF (-187)

#define yypact_value_is_default(Yyn) \
  ((Yyn) == YYPACT_NINF)

#define YYTABLE_NINF (-39)

#define yytable_value_is_error(Yyn) \
  0

/* YYPACT[STATE-NUM] -- Index in YYTABLE of the portion describing
   STATE-NUM.  */
static const yytype_int16 yypact[] =
{
      -7,  -187,  -187,     4,    56,   -23,    27,  -187,    11,  -187,
      56,    56,   -17,    56,    56,   303,    13,    60,    59,  -187,
    -187,   102,    37,  -187,  -187,  -187,  -187,  -187,  -187,  -187,
     102,   102,   102,    13,    50,    13,    13,    13,  -187,    78,
    -187,  -187,    79,  -187,    95,   179,    96,  -187,  -187,  -187,
    -187,  -187,   151,   193,   312,   312,   193,   102,    54,   143,
     -27,  -187,   154,  -187,   135,  -187,   174,  -187,  -187,   199,
      81,  -187,   -17,  -187,   252,   180,  -187,  -187,  -187,   129,
       9,  -187,   102,    23,  -187,   139,  -187,   137,   146,   102,
    -187,   102,   157,   199,    73,  -187,   232,    53,   159,  -187,
     199,   199,   199,   199,   199,   199,   199,   199,    81,    81,
     102,   164,   312,  -187,   190,  -187,  -187,  -187,    47,  -187,
    -187,   192,   200,   134,  -187,  -187,  -187,   248,   248,    73,
      73,  -187,  -187,   248,   248,   252,  -187,  -187,    76,  -187,
    -187,  -187,   198,   102,   102,  -187,  -187,   230,   204,   205,
    -187,  -187,   112,    10,   207,   218,   209,   217,   202,  -187,
     242,   271,  -187,  -187,  -187,  -187,  -187,  -187,  -187,   219,
     312,  -187,  -187,  -187,    81,   271,    -7,   193,   193,    72,
    -187,  -187,   193,   102,    70,   235,   233,   234,   312,   250,
     258,  -187,   259,   262,   169,   236,   266,   255,   210,   102,
     280,  -187,  -187,  -187,   271,  -187,   279,   210,   284,   285,
     305,   288,    81,   159,   227,   193,  -187,   213,  -187,    93,
     287,   290,   289,   291,   292,  -187,   294,   315,  -187,   193,
     297,   193,   271,   271,  -187,    38,  -187,  -187,  -187,   271,
    -187,  -187,  -187,   298,   271,   300,    81,   301,  -187,   132,
    -187,   304,   271,   302,  -187
};

/* YYDEFACT[STATE-NUM] -- Default reduction number in state STATE-NUM.
   Performed when YYTABLE does not specify something else to do.  Zero
   means the default is an error.  */
static const yytype_int8 yydefact[] =
{
     111,   109,   110,     0,   111,     0,     0,     1,     0,     2,
     111,   111,    23,   111,   111,     0,   111,     0,     0,     3,
       5,     0,     0,     4,     6,   104,   107,   105,   106,   108,
       0,     0,     0,   111,     0,   111,   111,   111,     9,     0,
     103,   102,     0,    18,     0,    21,     0,    12,     8,    10,
      11,    13,     0,     0,    30,    30,     0,     0,     0,     0,
       0,    76,     0,    71,   102,    72,     0,    96,    97,     0,
       0,    79,    23,    73,    69,    70,    87,    86,    77,    78,
       0,    29,     0,     0,    19,    21,    17,     0,     0,    33,
      98,     0,     0,     0,    81,    78,     0,     0,    87,    22,
       0,     0,     0,     0,     0,     0,     0,     0,     0,     0,
      33,     0,     0,    34,     0,    20,    48,    49,     0,    32,
      35,    15,     0,     0,    80,    88,    91,    94,    95,    82,
      83,    84,    85,    92,    93,     0,    89,    90,     0,    25,
      28,    25,     0,     0,    33,    75,    74,   111,     0,     0,
      50,    31,     0,   102,     0,     0,     0,     0,     0,    24,
     101,    38,    43,    41,    42,    44,    45,    46,    47,     0,
       0,    27,    26,    16,     0,    38,   111,     0,     0,     0,
      36,    37,     0,     0,     0,    39,     0,     0,     0,     0,
       0,   100,     0,     0,    21,     0,     0,     0,     0,     0,
       0,    52,    99,    51,    38,    40,     0,     0,     0,     0,
       0,     0,     0,     0,     0,     0,    64,    66,    58,     0,
       0,     0,     0,     0,     0,    63,     0,    60,    67,     0,
       0,     0,    38,    38,    61,     0,    57,    55,    54,    38,
      53,    62,    65,     0,    38,     0,     0,     0,    68,     0,
      59,     0,    38,     0,    56
};

/* YYPGOTO[NTERM-NUM].  */
static const yytype_int16 yypgoto[] =
{
    -187,  -187,   211,   131,  -187,   116,  -142,   -10,   175,   265,
     282,  -187,   269,   306,  -107,   240,   212,   215,  -133,  -186,
    -187,   281,   299,  -187,  -187,  -187,  -187,  -187,  -187,  -187,
     136,  -187,  -187,  -187,  -187,   -44,   307,   -62,   -57,   -68,
    -187,  -187,  -187,   -21,     0,     1
};

/* YYDEFGOTO[NTERM-NUM].  */
static const yytype_uint8 yydefgoto[] =
{
       0,     3,     9,    10,     5,    34,    71,    11,    12,    58,
      22,   147,    13,    80,   118,    81,   119,   148,   185,   186,
     161,   162,   163,   164,   221,   165,   236,   227,   237,   166,
     216,   217,   226,   167,   168,    72,    73,    74,    75,    76,
      77,    78,   180,    95,    82,    15
};

/* YYTABLE[YYPACT[STATE-NUM]] -- What to do in state STATE-NUM.  If
   positive, shift that token.  If negative, reduce the rule whose
   number is the opposite.  If YYTABLE_NINF, syntax error.  */
static const yytype_int16 yytable[] =
{
      42,     6,    98,   138,     7,   158,    35,    94,    96,    44,
      45,    46,    84,    97,   160,    31,    40,    41,   211,   158,
      32,    88,    16,    35,    89,    35,    35,    35,   181,     1,
       2,   123,    79,   158,    21,    79,    85,   152,   127,   128,
     129,   130,   131,   132,   133,   134,   135,   135,    17,     1,
       2,   136,   137,   245,    91,    18,    -7,   111,   247,   -14,
     112,   113,   158,   243,   108,   109,   253,    38,   120,     8,
     121,   114,   220,    39,   112,    61,    62,    63,    40,    64,
      65,   108,   109,   244,    61,    62,    43,    40,    64,   120,
     158,   158,     1,     2,    66,   142,    48,   158,   143,   241,
     242,   125,   158,    86,   108,   109,    67,    68,    40,    41,
     158,    69,   135,   104,   105,    67,    68,   184,   195,    70,
      69,   191,   120,   120,   146,    52,   169,   143,    70,    53,
     208,     4,    46,   189,   190,   192,   135,   159,   193,   213,
     169,   228,    54,   108,   109,    96,    59,    33,   170,    47,
     135,    49,    50,    51,   169,   219,    79,    79,    79,    60,
     173,    79,   194,   143,    33,    66,    33,    33,    33,    90,
     183,   223,   102,   103,   104,   105,   110,   188,   209,    91,
     251,    92,   124,   169,   135,   238,   116,   240,   199,   249,
      57,   108,   109,   222,    79,   117,    61,    62,    63,    40,
      64,    65,    61,    62,   122,    40,    64,   126,    79,   139,
      79,   169,   169,    61,    62,    66,    40,    64,   169,    56,
      57,    19,    20,   169,    23,    24,    55,    67,    68,    56,
      57,   169,    69,    40,    64,   141,    40,   153,    69,   144,
      70,   100,   101,     8,   215,   224,    93,   150,   145,    69,
     171,   172,   178,   -38,   174,   154,   176,   207,   155,   156,
     157,   100,   101,   175,   177,   179,     1,     2,   196,   182,
     102,   103,   104,   105,   106,   107,   -38,    40,   153,   197,
     124,   204,   206,   198,     8,    36,   102,   103,   104,   105,
     102,   103,   104,   105,   106,   107,   154,    37,   200,   155,
     156,   157,    36,    14,    36,    36,    36,   201,   202,    14,
      14,   203,    14,    14,    37,   205,    37,    37,    37,    25,
      26,    27,    28,    29,    17,   210,   212,    30,    25,    26,
      27,    28,    29,   214,   218,    56,   215,   229,   230,   231,
     234,   235,   239,   232,   233,   246,   248,   250,   254,   252,
     115,   187,   140,   225,    99,   151,   149,     0,     0,     0,
       0,    83,     0,     0,     0,     0,    87
};

static const yytype_int16 yycheck[] =
{
      21,     0,    70,   110,     0,   147,    16,    69,    70,    30,
      31,    32,    56,    70,   147,    15,     6,     7,   204,   161,
       7,    48,    45,    33,    51,    35,    36,    37,   161,    36,
      37,    93,    53,   175,    51,    56,    57,   144,   100,   101,
     102,   103,   104,   105,   106,   107,   108,   109,    21,    36,
      37,   108,   109,   239,    44,    44,     0,    48,   244,    46,
      51,    82,   204,    25,    11,    12,   252,     7,    89,    13,
      91,    48,   214,    14,    51,     3,     4,     5,     6,     7,
       8,    11,    12,    45,     3,     4,    49,     6,     7,   110,
     232,   233,    36,    37,    22,    48,    46,   239,    51,   232,
     233,    48,   244,    49,    11,    12,    34,    35,     6,     7,
     252,    39,   174,    40,    41,    34,    35,   174,    48,    47,
      39,    49,   143,   144,    48,    47,   147,    51,    47,    50,
     198,     0,   153,   177,   178,   179,   198,   147,   182,   207,
     161,    48,    47,    11,    12,   207,    50,    16,   147,    33,
     212,    35,    36,    37,   175,   212,   177,   178,   179,     8,
      48,   182,   183,    51,    33,    22,    35,    36,    37,    15,
     170,   215,    38,    39,    40,    41,    47,   176,   199,    44,
      48,     7,    48,   204,   246,   229,    49,   231,   188,   246,
      51,    11,    12,   214,   215,    49,     3,     4,     5,     6,
       7,     8,     3,     4,    47,     6,     7,    48,   229,    45,
     231,   232,   233,     3,     4,    22,     6,     7,   239,    50,
      51,    10,    11,   244,    13,    14,    47,    34,    35,    50,
      51,   252,    39,     6,     7,    45,     6,     7,    39,    47,
      47,     9,    10,    13,    31,    32,    47,    49,    48,    39,
      46,    46,    50,    23,    47,    25,    47,    47,    28,    29,
      30,     9,    10,    45,    47,    23,    36,    37,    33,    50,
      38,    39,    40,    41,    42,    43,    46,     6,     7,    46,
      48,    45,    27,    49,    13,    16,    38,    39,    40,    41,
      38,    39,    40,    41,    42,    43,    25,    16,    48,    28,
      29,    30,    33,     4,    35,    36,    37,    49,    49,    10,
      11,    49,    13,    14,    33,    49,    35,    36,    37,    16,
      17,    18,    19,    20,    21,    45,    47,    24,    16,    17,
      18,    19,    20,    49,    46,    50,    31,    50,    48,    50,
      46,    26,    45,    52,    52,    47,    46,    46,    46,    45,
      85,   176,   112,   217,    72,   143,   141,    -1,    -1,    -1,
      -1,    55,    -1,    -1,    -1,    -1,    59
};

/* YYSTOS[STATE-NUM] -- The symbol kind of the accessing symbol of
   state STATE-NUM.  */
static const yytype_int8 yystos[] =
{
       0,    36,    37,    54,    56,    57,    98,     0,    13,    55,
      56,    60,    61,    65,    75,    98,    45,    21,    44,    55,
      55,    51,    63,    55,    55,    16,    17,    18,    19,    20,
      24,    97,     7,    56,    58,    60,    65,    74,     7,    14,
       6,     7,    96,    49,    96,    96,    96,    58,    46,    58,
      58,    58,    47,    50,    47,    47,    50,    51,    62,    50,
       8,     3,     4,     5,     7,     8,    22,    34,    35,    39,
      47,    59,    88,    89,    90,    91,    92,    93,    94,    96,
      66,    68,    97,    66,    88,    96,    49,    89,    48,    51,
      15,    44,     7,    47,    90,    96,    90,    91,    92,    63,
       9,    10,    38,    39,    40,    41,    42,    43,    11,    12,
      47,    48,    51,    96,    48,    62,    49,    49,    67,    69,
      96,    96,    47,    90,    48,    48,    48,    90,    90,    90,
      90,    90,    90,    90,    90,    90,    91,    91,    67,    45,
      68,    45,    48,    51,    47,    48,    48,    64,    70,    70,
      49,    69,    67,     7,    25,    28,    29,    30,    59,    60,
      71,    73,    74,    75,    76,    78,    82,    86,    87,    96,
      98,    46,    46,    48,    47,    45,    47,    47,    50,    23,
      95,    71,    50,    97,    91,    71,    72,    61,    98,    88,
      88,    49,    88,    88,    96,    48,    33,    46,    49,    97,
      48,    49,    49,    49,    45,    49,    27,    47,    92,    96,
      45,    72,    47,    92,    49,    31,    83,    84,    46,    91,
      59,    77,    96,    88,    32,    83,    85,    80,    48,    50,
      48,    50,    52,    52,    46,    26,    79,    81,    88,    45,
      88,    71,    71,    25,    45,    72,    47,    72,    46,    91,
      46,    48,    45,    72,    46
};

/* YYR1[RULE-NUM] -- Symbol kind of the left-hand side of rule RULE-NUM.  */
static const yytype_int8 yyr1[] =
{
       0,    53,    54,    55,    55,    55,    55,    55,    56,    57,
      58,    58,    58,    58,    58,    59,    59,    60,    60,    61,
      62,    62,    63,    63,    64,    64,    65,    65,    66,    66,
      66,    67,    67,    67,    68,    69,    70,    71,    71,    72,
      72,    73,    73,    73,    73,    73,    73,    73,    74,    75,
      75,    76,    76,    77,    77,    78,    79,    80,    80,    81,
      81,    82,    83,    84,    84,    85,    85,    86,    87,    88,
      88,    88,    88,    88,    88,    89,    90,    90,    90,    90,
      90,    90,    90,    90,    90,    90,    91,    91,    91,    91,
      91,    92,    92,    92,    92,    92,    93,    93,    94,    95,
      95,    95,    96,    96,    97,    97,    97,    97,    97,    98,
      98,    98
};

/* YYR2[RULE-NUM] -- Number of symbols on the right-hand side of rule RULE-NUM.  */
static const yytype_int8 yyr2[] =
{
       0,     2,     2,     2,     2,     2,     2,     0,     4,     3,
       2,     2,     2,     2,     0,     3,     6,     5,     3,     5,
       3,     0,     5,     0,     2,     0,     9,     9,     3,     1,
       0,     3,     1,     0,     2,     1,     3,     2,     0,     1,
       3,     1,     1,     1,     1,     1,     1,     1,     5,     7,
       9,     4,     4,     3,     3,     9,     8,     2,     0,     4,
       0,     8,     4,     2,     1,     3,     0,     8,    11,     1,
       1,     1,     1,     1,     4,     4,     1,     1,     1,     1,
       3,     2,     3,     3,     3,     3,     1,     1,     3,     3,
       3,     3,     3,     3,     3,     3,     1,     1,     2,     3,
       2,     0,     1,     1,     1,     1,     1,     1,     1,     1,
       1,     0
};


enum { YYENOMEM = -2 };

#define yyerrok         (yyerrstatus = 0)
#define yyclearin       (yychar = YYEMPTY)

#define YYACCEPT        goto yyacceptlab
#define YYABORT         goto yyabortlab
#define YYERROR         goto yyerrorlab
#define YYNOMEM         goto yyexhaustedlab


#define YYRECOVERING()  (!!yyerrstatus)

#define YYBACKUP(Token, Value)                                    \
  do                                                              \
    if (yychar == YYEMPTY)                                        \
      {                                                           \
        yychar = (Token);                                         \
        yylval = (Value);                                         \
        YYPOPSTACK (yylen);                                       \
        yystate = *yyssp;                                         \
        goto yybackup;                                            \
      }                                                           \
    else                                                          \
      {                                                           \
        yyerror (YY_("syntax error: cannot back up")); \
        YYERROR;                                                  \
      }                                                           \
  while (0)

/* Backward compatibility with an undocumented macro.
   Use YYerror or YYUNDEF. */
#define YYERRCODE YYUNDEF


/* Enable debugging if requested.  */
#if YYDEBUG

# ifndef YYFPRINTF
#  include <stdio.h> /* INFRINGES ON USER NAME SPACE */
#  define YYFPRINTF fprintf
# endif

# define YYDPRINTF(Args)                        \
do {                                            \
  if (yydebug)                                  \
    YYFPRINTF Args;                             \
} while (0)




# define YY_SYMBOL_PRINT(Title, Kind, Value, Location)                    \
do {                                                                      \
  if (yydebug)                                                            \
    {                                                                     \
      YYFPRINTF (stderr, "%s ", Title);                                   \
      yy_symbol_print (stderr,                                            \
                  Kind, Value); \
      YYFPRINTF (stderr, "\n");                                           \
    }                                                                     \
} while (0)


/*-----------------------------------.
| Print this symbol's value on YYO.  |
`-----------------------------------*/

static void
yy_symbol_value_print (FILE *yyo,
                       yysymbol_kind_t yykind, YYSTYPE const * const yyvaluep)
{
  FILE *yyoutput = yyo;
  YY_USE (yyoutput);
  if (!yyvaluep)
    return;
  YY_IGNORE_MAYBE_UNINITIALIZED_BEGIN
  YY_USE (yykind);
  YY_IGNORE_MAYBE_UNINITIALIZED_END
}


/*---------------------------.
| Print this symbol on YYO.  |
`---------------------------*/

static void
yy_symbol_print (FILE *yyo,
                 yysymbol_kind_t yykind, YYSTYPE const * const yyvaluep)
{
  YYFPRINTF (yyo, "%s %s (",
             yykind < YYNTOKENS ? "token" : "nterm", yysymbol_name (yykind));

  yy_symbol_value_print (yyo, yykind, yyvaluep);
  YYFPRINTF (yyo, ")");
}

/*------------------------------------------------------------------.
| yy_stack_print -- Print the state stack from its BOTTOM up to its |
| TOP (included).                                                   |
`------------------------------------------------------------------*/

static void
yy_stack_print (yy_state_t *yybottom, yy_state_t *yytop)
{
  YYFPRINTF (stderr, "Stack now");
  for (; yybottom <= yytop; yybottom++)
    {
      int yybot = *yybottom;
      YYFPRINTF (stderr, " %d", yybot);
    }
  YYFPRINTF (stderr, "\n");
}

# define YY_STACK_PRINT(Bottom, Top)                            \
do {                                                            \
  if (yydebug)                                                  \
    yy_stack_print ((Bottom), (Top));                           \
} while (0)


/*------------------------------------------------.
| Report that the YYRULE is going to be reduced.  |
`------------------------------------------------*/

static void
yy_reduce_print (yy_state_t *yyssp, YYSTYPE *yyvsp,
                 int yyrule)
{
  int yylno = yyrline[yyrule];
  int yynrhs = yyr2[yyrule];
  int yyi;
  YYFPRINTF (stderr, "Reducing stack by rule %d (line %d):\n",
             yyrule - 1, yylno);
  /* The symbols being reduced.  */
  for (yyi = 0; yyi < yynrhs; yyi++)
    {
      YYFPRINTF (stderr, "   $%d = ", yyi + 1);
      yy_symbol_print (stderr,
                       YY_ACCESSING_SYMBOL (+yyssp[yyi + 1 - yynrhs]),
                       &yyvsp[(yyi + 1) - (yynrhs)]);
      YYFPRINTF (stderr, "\n");
    }
}

# define YY_REDUCE_PRINT(Rule)          \
do {                                    \
  if (yydebug)                          \
    yy_reduce_print (yyssp, yyvsp, Rule); \
} while (0)

/* Nonzero means print parse trace.  It is left uninitialized so that
   multiple parsers can coexist.  */
int yydebug;
#else /* !YYDEBUG */
# define YYDPRINTF(Args) ((void) 0)
# define YY_SYMBOL_PRINT(Title, Kind, Value, Location)
# define YY_STACK_PRINT(Bottom, Top)
# define YY_REDUCE_PRINT(Rule)
#endif /* !YYDEBUG */


/* YYINITDEPTH -- initial size of the parser's stacks.  */
#ifndef YYINITDEPTH
# define YYINITDEPTH 200
#endif

/* YYMAXDEPTH -- maximum size the stacks can grow to (effective only
   if the built-in stack extension method is used).

   Do not make this value too large; the results are undefined if
   YYSTACK_ALLOC_MAXIMUM < YYSTACK_BYTES (YYMAXDEPTH)
   evaluated with infinite-precision integer arithmetic.  */

#ifndef YYMAXDEPTH
# define YYMAXDEPTH 10000
#endif


/* Context of a parse error.  */
typedef struct
{
  yy_state_t *yyssp;
  yysymbol_kind_t yytoken;
} yypcontext_t;

/* Put in YYARG at most YYARGN of the expected tokens given the
   current YYCTX, and return the number of tokens stored in YYARG.  If
   YYARG is null, return the number of expected tokens (guaranteed to
   be less than YYNTOKENS).  Return YYENOMEM on memory exhaustion.
   Return 0 if there are more than YYARGN expected tokens, yet fill
   YYARG up to YYARGN. */
static int
yypcontext_expected_tokens (const yypcontext_t *yyctx,
                            yysymbol_kind_t yyarg[], int yyargn)
{
  /* Actual size of YYARG. */
  int yycount = 0;
  int yyn = yypact[+*yyctx->yyssp];
  if (!yypact_value_is_default (yyn))
    {
      /* Start YYX at -YYN if negative to avoid negative indexes in
         YYCHECK.  In other words, skip the first -YYN actions for
         this state because they are default actions.  */
      int yyxbegin = yyn < 0 ? -yyn : 0;
      /* Stay within bounds of both yycheck and yytname.  */
      int yychecklim = YYLAST - yyn + 1;
      int yyxend = yychecklim < YYNTOKENS ? yychecklim : YYNTOKENS;
      int yyx;
      for (yyx = yyxbegin; yyx < yyxend; ++yyx)
        if (yycheck[yyx + yyn] == yyx && yyx != YYSYMBOL_YYerror
            && !yytable_value_is_error (yytable[yyx + yyn]))
          {
            if (!yyarg)
              ++yycount;
            else if (yycount == yyargn)
              return 0;
            else
              yyarg[yycount++] = YY_CAST (yysymbol_kind_t, yyx);
          }
    }
  if (yyarg && yycount == 0 && 0 < yyargn)
    yyarg[0] = YYSYMBOL_YYEMPTY;
  return yycount;
}




#ifndef yystrlen
# if defined __GLIBC__ && defined _STRING_H
#  define yystrlen(S) (YY_CAST (YYPTRDIFF_T, strlen (S)))
# else
/* Return the length of YYSTR.  */
static YYPTRDIFF_T
yystrlen (const char *yystr)
{
  YYPTRDIFF_T yylen;
  for (yylen = 0; yystr[yylen]; yylen++)
    continue;
  return yylen;
}
# endif
#endif

#ifndef yystpcpy
# if defined __GLIBC__ && defined _STRING_H && defined _GNU_SOURCE
#  define yystpcpy stpcpy
# else
/* Copy YYSRC to YYDEST, returning the address of the terminating '\0' in
   YYDEST.  */
static char *
yystpcpy (char *yydest, const char *yysrc)
{
  char *yyd = yydest;
  const char *yys = yysrc;

  while ((*yyd++ = *yys++) != '\0')
    continue;

  return yyd - 1;
}
# endif
#endif

#ifndef yytnamerr
/* Copy to YYRES the contents of YYSTR after stripping away unnecessary
   quotes and backslashes, so that it's suitable for yyerror.  The
   heuristic is that double-quoting is unnecessary unless the string
   contains an apostrophe, a comma, or backslash (other than
   backslash-backslash).  YYSTR is taken from yytname.  If YYRES is
   null, do not copy; instead, return the length of what the result
   would have been.  */
static YYPTRDIFF_T
yytnamerr (char *yyres, const char *yystr)
{
  if (*yystr == '"')
    {
      YYPTRDIFF_T yyn = 0;
      char const *yyp = yystr;
      for (;;)
        switch (*++yyp)
          {
          case '\'':
          case ',':
            goto do_not_strip_quotes;

          case '\\':
            if (*++yyp != '\\')
              goto do_not_strip_quotes;
            else
              goto append;

          append:
          default:
            if (yyres)
              yyres[yyn] = *yyp;
            yyn++;
            break;

          case '"':
            if (yyres)
              yyres[yyn] = '\0';
            return yyn;
          }
    do_not_strip_quotes: ;
    }

  if (yyres)
    return yystpcpy (yyres, yystr) - yyres;
  else
    return yystrlen (yystr);
}
#endif


static int
yy_syntax_error_arguments (const yypcontext_t *yyctx,
                           yysymbol_kind_t yyarg[], int yyargn)
{
  /* Actual size of YYARG. */
  int yycount = 0;
  /* There are many possibilities here to consider:
     - If this state is a consistent state with a default action, then
       the only way this function was invoked is if the default action
       is an error action.  In that case, don't check for expected
       tokens because there are none.
     - The only way there can be no lookahead present (in yychar) is if
       this state is a consistent state with a default action.  Thus,
       detecting the absence of a lookahead is sufficient to determine
       that there is no unexpected or expected token to report.  In that
       case, just report a simple "syntax error".
     - Don't assume there isn't a lookahead just because this state is a
       consistent state with a default action.  There might have been a
       previous inconsistent state, consistent state with a non-default
       action, or user semantic action that manipulated yychar.
     - Of course, the expected token list depends on states to have
       correct lookahead information, and it depends on the parser not
       to perform extra reductions after fetching a lookahead from the
       scanner and before detecting a syntax error.  Thus, state merging
       (from LALR or IELR) and default reductions corrupt the expected
       token list.  However, the list is correct for canonical LR with
       one exception: it will still contain any token that will not be
       accepted due to an error action in a later state.
  */
  if (yyctx->yytoken != YYSYMBOL_YYEMPTY)
    {
      int yyn;
      if (yyarg)
        yyarg[yycount] = yyctx->yytoken;
      ++yycount;
      yyn = yypcontext_expected_tokens (yyctx,
                                        yyarg ? yyarg + 1 : yyarg, yyargn - 1);
      if (yyn == YYENOMEM)
        return YYENOMEM;
      else
        yycount += yyn;
    }
  return yycount;
}

/* Copy into *YYMSG, which is of size *YYMSG_ALLOC, an error message
   about the unexpected token YYTOKEN for the state stack whose top is
   YYSSP.

   Return 0 if *YYMSG was successfully written.  Return -1 if *YYMSG is
   not large enough to hold the message.  In that case, also set
   *YYMSG_ALLOC to the required number of bytes.  Return YYENOMEM if the
   required number of bytes is too large to store.  */
static int
yysyntax_error (YYPTRDIFF_T *yymsg_alloc, char **yymsg,
                const yypcontext_t *yyctx)
{
  enum { YYARGS_MAX = 5 };
  /* Internationalized format string. */
  const char *yyformat = YY_NULLPTR;
  /* Arguments of yyformat: reported tokens (one for the "unexpected",
     one per "expected"). */
  yysymbol_kind_t yyarg[YYARGS_MAX];
  /* Cumulated lengths of YYARG.  */
  YYPTRDIFF_T yysize = 0;

  /* Actual size of YYARG. */
  int yycount = yy_syntax_error_arguments (yyctx, yyarg, YYARGS_MAX);
  if (yycount == YYENOMEM)
    return YYENOMEM;

  switch (yycount)
    {
#define YYCASE_(N, S)                       \
      case N:                               \
        yyformat = S;                       \
        break
    default: /* Avoid compiler warnings. */
      YYCASE_(0, YY_("syntax error"));
      YYCASE_(1, YY_("syntax error, unexpected %s"));
      YYCASE_(2, YY_("syntax error, unexpected %s, expecting %s"));
      YYCASE_(3, YY_("syntax error, unexpected %s, expecting %s or %s"));
      YYCASE_(4, YY_("syntax error, unexpected %s, expecting %s or %s or %s"));
      YYCASE_(5, YY_("syntax error, unexpected %s, expecting %s or %s or %s or %s"));
#undef YYCASE_
    }

  /* Compute error message size.  Don't count the "%s"s, but reserve
     room for the terminator.  */
  yysize = yystrlen (yyformat) - 2 * yycount + 1;
  {
    int yyi;
    for (yyi = 0; yyi < yycount; ++yyi)
      {
        YYPTRDIFF_T yysize1
          = yysize + yytnamerr (YY_NULLPTR, yytname[yyarg[yyi]]);
        if (yysize <= yysize1 && yysize1 <= YYSTACK_ALLOC_MAXIMUM)
          yysize = yysize1;
        else
          return YYENOMEM;
      }
  }

  if (*yymsg_alloc < yysize)
    {
      *yymsg_alloc = 2 * yysize;
      if (! (yysize <= *yymsg_alloc
             && *yymsg_alloc <= YYSTACK_ALLOC_MAXIMUM))
        *yymsg_alloc = YYSTACK_ALLOC_MAXIMUM;
      return -1;
    }

  /* Avoid sprintf, as that infringes on the user's name space.
     Don't have undefined behavior even if the translation
     produced a string with the wrong number of "%s"s.  */
  {
    char *yyp = *yymsg;
    int yyi = 0;
    while ((*yyp = *yyformat) != '\0')
      if (*yyp == '%' && yyformat[1] == 's' && yyi < yycount)
        {
          yyp += yytnamerr (yyp, yytname[yyarg[yyi++]]);
          yyformat += 2;
        }
      else
        {
          ++yyp;
          ++yyformat;
        }
  }
  return 0;
}


/*-----------------------------------------------.
| Release the memory associated to this symbol.  |
`-----------------------------------------------*/

static void
yydestruct (const char *yymsg,
            yysymbol_kind_t yykind, YYSTYPE *yyvaluep)
{
  YY_USE (yyvaluep);
  if (!yymsg)
    yymsg = "Deleting";
  YY_SYMBOL_PRINT (yymsg, yykind, yyvaluep, yylocationp);

  YY_IGNORE_MAYBE_UNINITIALIZED_BEGIN
  YY_USE (yykind);
  YY_IGNORE_MAYBE_UNINITIALIZED_END
}


/* Lookahead token kind.  */
int yychar;

/* The semantic value of the lookahead symbol.  */
YYSTYPE yylval;
/* Number of syntax errors so far.  */
int yynerrs;




/*----------.
| yyparse.  |
`----------*/

int
yyparse (void)
{
    yy_state_fast_t yystate = 0;
    /* Number of tokens to shift before error messages enabled.  */
    int yyerrstatus = 0;

    /* Refer to the stacks through separate pointers, to allow yyoverflow
       to reallocate them elsewhere.  */

    /* Their size.  */
    YYPTRDIFF_T yystacksize = YYINITDEPTH;

    /* The state stack: array, bottom, top.  */
    yy_state_t yyssa[YYINITDEPTH];
    yy_state_t *yyss = yyssa;
    yy_state_t *yyssp = yyss;

    /* The semantic value stack: array, bottom, top.  */
    YYSTYPE yyvsa[YYINITDEPTH];
    YYSTYPE *yyvs = yyvsa;
    YYSTYPE *yyvsp = yyvs;

  int yyn;
  /* The return value of yyparse.  */
  int yyresult;
  /* Lookahead symbol kind.  */
  yysymbol_kind_t yytoken = YYSYMBOL_YYEMPTY;
  /* The variables used to return semantic value and location from the
     action routines.  */
  YYSTYPE yyval;

  /* Buffer for error messages, and its allocated size.  */
  char yymsgbuf[128];
  char *yymsg = yymsgbuf;
  YYPTRDIFF_T yymsg_alloc = sizeof yymsgbuf;

#define YYPOPSTACK(N)   (yyvsp -= (N), yyssp -= (N))

  /* The number of symbols on the RHS of the reduced rule.
     Keep to zero when no symbol should be popped.  */
  int yylen = 0;

  YYDPRINTF ((stderr, "Starting parse\n"));

  yychar = YYEMPTY; /* Cause a token to be read.  */

  goto yysetstate;


/*------------------------------------------------------------.
| yynewstate -- push a new state, which is found in yystate.  |
`------------------------------------------------------------*/
yynewstate:
  /* In all cases, when you get here, the value and location stacks
     have just been pushed.  So pushing a state here evens the stacks.  */
  yyssp++;


/*--------------------------------------------------------------------.
| yysetstate -- set current state (the top of the stack) to yystate.  |
`--------------------------------------------------------------------*/
yysetstate:
  YYDPRINTF ((stderr, "Entering state %d\n", yystate));
  YY_ASSERT (0 <= yystate && yystate < YYNSTATES);
  YY_IGNORE_USELESS_CAST_BEGIN
  *yyssp = YY_CAST (yy_state_t, yystate);
  YY_IGNORE_USELESS_CAST_END
  YY_STACK_PRINT (yyss, yyssp);

  if (yyss + yystacksize - 1 <= yyssp)
#if !defined yyoverflow && !defined YYSTACK_RELOCATE
    YYNOMEM;
#else
    {
      /* Get the current used size of the three stacks, in elements.  */
      YYPTRDIFF_T yysize = yyssp - yyss + 1;

# if defined yyoverflow
      {
        /* Give user a chance to reallocate the stack.  Use copies of
           these so that the &'s don't force the real ones into
           memory.  */
        yy_state_t *yyss1 = yyss;
        YYSTYPE *yyvs1 = yyvs;

        /* Each stack pointer address is followed by the size of the
           data in use in that stack, in bytes.  This used to be a
           conditional around just the two extra args, but that might
           be undefined if yyoverflow is a macro.  */
        yyoverflow (YY_("memory exhausted"),
                    &yyss1, yysize * YYSIZEOF (*yyssp),
                    &yyvs1, yysize * YYSIZEOF (*yyvsp),
                    &yystacksize);
        yyss = yyss1;
        yyvs = yyvs1;
      }
# else /* defined YYSTACK_RELOCATE */
      /* Extend the stack our own way.  */
      if (YYMAXDEPTH <= yystacksize)
        YYNOMEM;
      yystacksize *= 2;
      if (YYMAXDEPTH < yystacksize)
        yystacksize = YYMAXDEPTH;

      {
        yy_state_t *yyss1 = yyss;
        union yyalloc *yyptr =
          YY_CAST (union yyalloc *,
                   YYSTACK_ALLOC (YY_CAST (YYSIZE_T, YYSTACK_BYTES (yystacksize))));
        if (! yyptr)
          YYNOMEM;
        YYSTACK_RELOCATE (yyss_alloc, yyss);
        YYSTACK_RELOCATE (yyvs_alloc, yyvs);
#  undef YYSTACK_RELOCATE
        if (yyss1 != yyssa)
          YYSTACK_FREE (yyss1);
      }
# endif

      yyssp = yyss + yysize - 1;
      yyvsp = yyvs + yysize - 1;

      YY_IGNORE_USELESS_CAST_BEGIN
      YYDPRINTF ((stderr, "Stack size increased to %ld\n",
                  YY_CAST (long, yystacksize)));
      YY_IGNORE_USELESS_CAST_END

      if (yyss + yystacksize - 1 <= yyssp)
        YYABORT;
    }
#endif /* !defined yyoverflow && !defined YYSTACK_RELOCATE */


  if (yystate == YYFINAL)
    YYACCEPT;

  goto yybackup;


/*-----------.
| yybackup.  |
`-----------*/
yybackup:
  /* Do appropriate processing given the current state.  Read a
     lookahead token if we need one and don't already have one.  */

  /* First try to decide what to do without reference to lookahead token.  */
  yyn = yypact[yystate];
  if (yypact_value_is_default (yyn))
    goto yydefault;

  /* Not known => get a lookahead token if don't already have one.  */

  /* YYCHAR is either empty, or end-of-input, or a valid lookahead.  */
  if (yychar == YYEMPTY)
    {
      YYDPRINTF ((stderr, "Reading a token\n"));
      yychar = yylex ();
    }

  if (yychar <= YYEOF)
    {
      yychar = YYEOF;
      yytoken = YYSYMBOL_YYEOF;
      YYDPRINTF ((stderr, "Now at end of input.\n"));
    }
  else if (yychar == YYerror)
    {
      /* The scanner already issued an error message, process directly
         to error recovery.  But do not keep the error token as
         lookahead, it is too special and may lead us to an endless
         loop in error recovery. */
      yychar = YYUNDEF;
      yytoken = YYSYMBOL_YYerror;
      goto yyerrlab1;
    }
  else
    {
      yytoken = YYTRANSLATE (yychar);
      YY_SYMBOL_PRINT ("Next token is", yytoken, &yylval, &yylloc);
    }

  /* If the proper action on seeing token YYTOKEN is to reduce or to
     detect an error, take that action.  */
  yyn += yytoken;
  if (yyn < 0 || YYLAST < yyn || yycheck[yyn] != yytoken)
    goto yydefault;
  yyn = yytable[yyn];
  if (yyn <= 0)
    {
      if (yytable_value_is_error (yyn))
        goto yyerrlab;
      yyn = -yyn;
      goto yyreduce;
    }

  /* Count tokens shifted since error; after three, turn off error
     status.  */
  if (yyerrstatus)
    yyerrstatus--;

  /* Shift the lookahead token.  */
  YY_SYMBOL_PRINT ("Shifting", yytoken, &yylval, &yylloc);
  yystate = yyn;
  YY_IGNORE_MAYBE_UNINITIALIZED_BEGIN
  *++yyvsp = yylval;
  YY_IGNORE_MAYBE_UNINITIALIZED_END

  /* Discard the shifted token.  */
  yychar = YYEMPTY;
  goto yynewstate;


/*-----------------------------------------------------------.
| yydefault -- do the default action for the current state.  |
`-----------------------------------------------------------*/
yydefault:
  yyn = yydefact[yystate];
  if (yyn == 0)
    goto yyerrlab;
  goto yyreduce;


/*-----------------------------.
| yyreduce -- do a reduction.  |
`-----------------------------*/
yyreduce:
  /* yyn is the number of a rule to reduce with.  */
  yylen = yyr2[yyn];

  /* If YYLEN is nonzero, implement the default value of the action:
     '$$ = $1'.

     Otherwise, the following line sets YYVAL to garbage.
     This behavior is undocumented and Bison
     users should not rely upon it.  Assigning to YYVAL
     unconditionally makes the parser a bit smaller, and it avoids a
     GCC warning that YYVAL may be used uninitialized.  */
  yyval = yyvsp[1-yylen];


  YY_REDUCE_PRINT (yyn);
  switch (yyn)
    {
  case 8: /* class_declaration: begin_class_declaration '{' class_body '}'  */
#line 83 "bison_parser.y"
            {
                if ( !parser_helper->in_class.empty() ) {
                    parser_helper->in_class.pop_back();
                }
            }
#line 1643 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 9: /* begin_class_declaration: access_modifier CLASS CLASS_NAME  */
#line 92 "bison_parser.y"
            {
                parser_helper->addClass((yyvsp[0].str_val), (yyvsp[-2].bool_val));
                parser_helper->in_class.push_back((yyvsp[0].str_val));
            }
#line 1652 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 15: /* class_member: CLASS_NAME '.' identifier  */
#line 108 "bison_parser.y"
            {
                const auto opt_error = parser_helper->getVariable((yyvsp[-2].str_val), (yyvsp[0].str_val));
                if(opt_error){
                    yyerror(opt_error.value().c_str());
                    YYERROR;
                }
            }
#line 1664 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 16: /* class_member: CLASS_NAME '.' identifier '(' method_call_parameter_list ')'  */
#line 116 "bison_parser.y"
            {
                const auto opt_error = parser_helper->getMethod((yyvsp[-5].str_val), (yyvsp[-3].str_val));
                if(opt_error){
                    yyerror(opt_error.value().c_str());
                    YYERROR;
                }
            }
#line 1676 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 17: /* variable_declaration: access_modifier data_type identifier opt_more_variables ';'  */
#line 138 "bison_parser.y"
        {
            parser_helper->addVariable((yyvsp[-2].str_val), temp_data_type, (yyvsp[-4].bool_val));
        }
#line 1684 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 19: /* variable_declaration_with_assign: access_modifier data_type identifier '=' expression  */
#line 146 "bison_parser.y"
            {
                parser_helper->addVariable((yyvsp[-2].str_val), temp_data_type, (yyvsp[-4].bool_val));
            }
#line 1692 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 20: /* opt_more_variables: ',' identifier opt_more_variables  */
#line 152 "bison_parser.y"
                                            { parser_helper->addVariable((yyvsp[-1].str_val), temp_data_type, temp_access_modifier); }
#line 1698 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 22: /* opt_more_variables_with_assign: ',' identifier '=' expression opt_more_variables_with_assign  */
#line 157 "bison_parser.y"
                                                                        { parser_helper->addVariable((yyvsp[-3].str_val), temp_data_type, temp_access_modifier); }
#line 1704 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 26: /* method_declaration: access_modifier data_type identifier '(' method_parameter_list ')' '{' method_body '}'  */
#line 170 "bison_parser.y"
            {
                parser_helper->addMethod((yyvsp[-6].str_val), temp_data_type ,(yyvsp[-8].bool_val));
            }
#line 1712 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 27: /* method_declaration: access_modifier VOID identifier '(' method_parameter_list ')' '{' method_body '}'  */
#line 174 "bison_parser.y"
            {
                parser_helper->addMethod((yyvsp[-6].str_val), DataType::VOID_TYPE,(yyvsp[-8].bool_val));
            }
#line 1720 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 51: /* assign_command: identifier '=' expression ';'  */
#line 237 "bison_parser.y"
        {
            const auto opt_error = parser_helper->getVariable(parser_helper->in_class.back(), (yyvsp[-3].str_val));
            if(opt_error){
                yyerror(opt_error.value().c_str());
                YYERROR;
            }
        }
#line 1732 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 74: /* expression: identifier '(' method_call_parameter_list ')'  */
#line 307 "bison_parser.y"
            {
                const auto opt_error = parser_helper->getMethod(parser_helper->in_class.back(), (yyvsp[-3].str_val));
                if(opt_error){
                    yyerror(opt_error.value().c_str());
                    YYERROR;
                }
            }
#line 1744 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 75: /* object_instation: NEW CLASS_NAME '(' ')'  */
#line 319 "bison_parser.y"
            {
                const auto opt_error = parser_helper->getClass((yyvsp[-2].str_val));
                if(opt_error){
                    yyerror(opt_error.value().c_str());
                    YYERROR;
                }
            }
#line 1756 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 96: /* bool_literal: TRUE  */
#line 358 "bison_parser.y"
                { (yyval.bool_val) = true; }
#line 1762 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 97: /* bool_literal: FALSE  */
#line 359 "bison_parser.y"
                 { (yyval.bool_val) = false; }
#line 1768 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 98: /* double_literal: DOUBLE_LITERAL DOUBLE_SP  */
#line 363 "bison_parser.y"
                                  { (yyval.double_val) = (yyvsp[-1].double_val); }
#line 1774 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 102: /* identifier: CLASS_NAME  */
#line 375 "bison_parser.y"
                    { (yyval.str_val) = (yyvsp[0].str_val); }
#line 1780 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 103: /* identifier: IDENTIFIER  */
#line 376 "bison_parser.y"
                    { (yyval.str_val) = (yyvsp[0].str_val); }
#line 1786 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 104: /* data_type: INT  */
#line 380 "bison_parser.y"
                { temp_data_type = DataType::INT_TYPE; }
#line 1792 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 105: /* data_type: DOUBLE  */
#line 381 "bison_parser.y"
                { temp_data_type = DataType::DOUBLE_TYPE; }
#line 1798 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 106: /* data_type: BOOLEAN  */
#line 382 "bison_parser.y"
                { temp_data_type = DataType::BOOL_TYPE; }
#line 1804 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 107: /* data_type: CHAR  */
#line 383 "bison_parser.y"
                { temp_data_type = DataType::CHAR_TYPE; }
#line 1810 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 108: /* data_type: STRING  */
#line 384 "bison_parser.y"
                { temp_data_type = DataType::STRING_TYPE; }
#line 1816 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 109: /* access_modifier: PUBLIC  */
#line 388 "bison_parser.y"
                    { (yyval.bool_val) = true; temp_access_modifier = true; }
#line 1822 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 110: /* access_modifier: PRIVATE  */
#line 389 "bison_parser.y"
                    { (yyval.bool_val) = false; temp_access_modifier = false; }
#line 1828 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;

  case 111: /* access_modifier: %empty  */
#line 390 "bison_parser.y"
                    { (yyval.bool_val) = true; temp_access_modifier = true; }
#line 1834 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"
    break;


#line 1838 "/home/vagelis/Desktop/parser_project/generated/bison_parser.tab.cpp"

      default: break;
    }
  /* User semantic actions sometimes alter yychar, and that requires
     that yytoken be updated with the new translation.  We take the
     approach of translating immediately before every use of yytoken.
     One alternative is translating here after every semantic action,
     but that translation would be missed if the semantic action invokes
     YYABORT, YYACCEPT, or YYERROR immediately after altering yychar or
     if it invokes YYBACKUP.  In the case of YYABORT or YYACCEPT, an
     incorrect destructor might then be invoked immediately.  In the
     case of YYERROR or YYBACKUP, subsequent parser actions might lead
     to an incorrect destructor call or verbose syntax error message
     before the lookahead is translated.  */
  YY_SYMBOL_PRINT ("-> $$ =", YY_CAST (yysymbol_kind_t, yyr1[yyn]), &yyval, &yyloc);

  YYPOPSTACK (yylen);
  yylen = 0;

  *++yyvsp = yyval;

  /* Now 'shift' the result of the reduction.  Determine what state
     that goes to, based on the state we popped back to and the rule
     number reduced by.  */
  {
    const int yylhs = yyr1[yyn] - YYNTOKENS;
    const int yyi = yypgoto[yylhs] + *yyssp;
    yystate = (0 <= yyi && yyi <= YYLAST && yycheck[yyi] == *yyssp
               ? yytable[yyi]
               : yydefgoto[yylhs]);
  }

  goto yynewstate;


/*--------------------------------------.
| yyerrlab -- here on detecting error.  |
`--------------------------------------*/
yyerrlab:
  /* Make sure we have latest lookahead translation.  See comments at
     user semantic actions for why this is necessary.  */
  yytoken = yychar == YYEMPTY ? YYSYMBOL_YYEMPTY : YYTRANSLATE (yychar);
  /* If not already recovering from an error, report this error.  */
  if (!yyerrstatus)
    {
      ++yynerrs;
      {
        yypcontext_t yyctx
          = {yyssp, yytoken};
        char const *yymsgp = YY_("syntax error");
        int yysyntax_error_status;
        yysyntax_error_status = yysyntax_error (&yymsg_alloc, &yymsg, &yyctx);
        if (yysyntax_error_status == 0)
          yymsgp = yymsg;
        else if (yysyntax_error_status == -1)
          {
            if (yymsg != yymsgbuf)
              YYSTACK_FREE (yymsg);
            yymsg = YY_CAST (char *,
                             YYSTACK_ALLOC (YY_CAST (YYSIZE_T, yymsg_alloc)));
            if (yymsg)
              {
                yysyntax_error_status
                  = yysyntax_error (&yymsg_alloc, &yymsg, &yyctx);
                yymsgp = yymsg;
              }
            else
              {
                yymsg = yymsgbuf;
                yymsg_alloc = sizeof yymsgbuf;
                yysyntax_error_status = YYENOMEM;
              }
          }
        yyerror (yymsgp);
        if (yysyntax_error_status == YYENOMEM)
          YYNOMEM;
      }
    }

  if (yyerrstatus == 3)
    {
      /* If just tried and failed to reuse lookahead token after an
         error, discard it.  */

      if (yychar <= YYEOF)
        {
          /* Return failure if at end of input.  */
          if (yychar == YYEOF)
            YYABORT;
        }
      else
        {
          yydestruct ("Error: discarding",
                      yytoken, &yylval);
          yychar = YYEMPTY;
        }
    }

  /* Else will try to reuse lookahead token after shifting the error
     token.  */
  goto yyerrlab1;


/*---------------------------------------------------.
| yyerrorlab -- error raised explicitly by YYERROR.  |
`---------------------------------------------------*/
yyerrorlab:
  /* Pacify compilers when the user code never invokes YYERROR and the
     label yyerrorlab therefore never appears in user code.  */
  if (0)
    YYERROR;
  ++yynerrs;

  /* Do not reclaim the symbols of the rule whose action triggered
     this YYERROR.  */
  YYPOPSTACK (yylen);
  yylen = 0;
  YY_STACK_PRINT (yyss, yyssp);
  yystate = *yyssp;
  goto yyerrlab1;


/*-------------------------------------------------------------.
| yyerrlab1 -- common code for both syntax error and YYERROR.  |
`-------------------------------------------------------------*/
yyerrlab1:
  yyerrstatus = 3;      /* Each real token shifted decrements this.  */

  /* Pop stack until we find a state that shifts the error token.  */
  for (;;)
    {
      yyn = yypact[yystate];
      if (!yypact_value_is_default (yyn))
        {
          yyn += YYSYMBOL_YYerror;
          if (0 <= yyn && yyn <= YYLAST && yycheck[yyn] == YYSYMBOL_YYerror)
            {
              yyn = yytable[yyn];
              if (0 < yyn)
                break;
            }
        }

      /* Pop the current state because it cannot handle the error token.  */
      if (yyssp == yyss)
        YYABORT;


      yydestruct ("Error: popping",
                  YY_ACCESSING_SYMBOL (yystate), yyvsp);
      YYPOPSTACK (1);
      yystate = *yyssp;
      YY_STACK_PRINT (yyss, yyssp);
    }

  YY_IGNORE_MAYBE_UNINITIALIZED_BEGIN
  *++yyvsp = yylval;
  YY_IGNORE_MAYBE_UNINITIALIZED_END


  /* Shift the error token.  */
  YY_SYMBOL_PRINT ("Shifting", YY_ACCESSING_SYMBOL (yyn), yyvsp, yylsp);

  yystate = yyn;
  goto yynewstate;


/*-------------------------------------.
| yyacceptlab -- YYACCEPT comes here.  |
`-------------------------------------*/
yyacceptlab:
  yyresult = 0;
  goto yyreturnlab;


/*-----------------------------------.
| yyabortlab -- YYABORT comes here.  |
`-----------------------------------*/
yyabortlab:
  yyresult = 1;
  goto yyreturnlab;


/*-----------------------------------------------------------.
| yyexhaustedlab -- YYNOMEM (memory exhaustion) comes here.  |
`-----------------------------------------------------------*/
yyexhaustedlab:
  yyerror (YY_("memory exhausted"));
  yyresult = 2;
  goto yyreturnlab;


/*----------------------------------------------------------.
| yyreturnlab -- parsing is finished, clean up and return.  |
`----------------------------------------------------------*/
yyreturnlab:
  if (yychar != YYEMPTY)
    {
      /* Make sure we have latest lookahead translation.  See comments at
         user semantic actions for why this is necessary.  */
      yytoken = YYTRANSLATE (yychar);
      yydestruct ("Cleanup: discarding lookahead",
                  yytoken, &yylval);
    }
  /* Do not reclaim the symbols of the rule whose action triggered
     this YYABORT or YYACCEPT.  */
  YYPOPSTACK (yylen);
  YY_STACK_PRINT (yyss, yyssp);
  while (yyssp != yyss)
    {
      yydestruct ("Cleanup: popping",
                  YY_ACCESSING_SYMBOL (+*yyssp), yyvsp);
      YYPOPSTACK (1);
    }
#ifndef yyoverflow
  if (yyss != yyssa)
    YYSTACK_FREE (yyss);
#endif
  if (yymsg != yymsgbuf)
    YYSTACK_FREE (yymsg);
  return yyresult;
}

#line 393 "bison_parser.y"


int yyerror(const char *s) {
    std::cerr << "Error at line " << yylineno << " : " << s << std::endl;
    return 0;
}
