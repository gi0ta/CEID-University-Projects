cmake_minimum_required(VERSION 3.10)

project(BisonFlexParser)

set(CMAKE_CXX_STANDARD 23)

find_package(FLEX REQUIRED)
find_package(BISON REQUIRED)

set(OUTPUT_DIR ${CMAKE_CURRENT_SOURCE_DIR}/generated)
file(MAKE_DIRECTORY ${OUTPUT_DIR})

FLEX_TARGET(MyLexer flex_lexer.l ${OUTPUT_DIR}/flex_lexer.yy.cpp)
BISON_TARGET(MyParser bison_parser.y ${OUTPUT_DIR}/bison_parser.tab.cpp
        # COMPILE_FLAGS "-Wcounterexamples"
)

ADD_FLEX_BISON_DEPENDENCY(MyLexer MyParser)

add_executable(parser main.cpp parser_helper.h ${BISON_MyParser_OUTPUTS} ${FLEX_MyLexer_OUTPUTS})

target_link_libraries(parser ${FLEX_LIBRARIES} ${BISON_LIBRARIES})

target_include_directories(parser PRIVATE ${OUTPUT_DIR})


