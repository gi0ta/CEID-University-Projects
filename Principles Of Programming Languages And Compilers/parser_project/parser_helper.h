#ifndef PARSER_HELPER_H
#define PARSER_HELPER_H

#include <iostream>
#include <string>
#include <map>
#include <optional>
#include <ostream>
#include <utility>
#include <vector>

enum DataType {
  INT_TYPE,
  DOUBLE_TYPE,
  CHAR_TYPE,
  STRING_TYPE,
  BOOL_TYPE,
  VOID_TYPE
};

class ParsedVariable {
  int _value{};

  std::string _name;
  DataType _type;
  bool _is_public;

public:
  ParsedVariable(std::string name, const DataType type, const bool is_public) : _name(std::move(name)),
    _type(type),
    _is_public(is_public) {
  }

  void set_value(const int value) { _value = value; }
  int get_value() const { return _value; }
  std::string name() const { return _name; }
  bool isPublic() const { return _is_public; }
};

class ParsedMethod {
  std::string _name;
  DataType _type;
  bool _is_public;

public:
  ParsedMethod(std::string name, const DataType type, const bool is_public) : _name(std::move(name)),
                                                                              _type(type),
                                                                              _is_public(is_public) {
  }

  std::string name() const { return _name; }
  bool isPublic() const { return _is_public; }
};

class ParsedClass {
  std::string _name;
  bool _is_public;

  std::vector<ParsedVariable> variables{};

  std::vector<ParsedMethod> methods{};

public:
  ParsedClass(std::string name, const bool is_public) : _name(std::move(name)),
                                                        _is_public(is_public) {
  }


  void addVariable(const ParsedVariable& parsed_var) {
    variables.push_back(parsed_var);
  }

  void addMethod(const ParsedMethod& parsed_method) {
    methods.push_back(parsed_method);
  }

  std::optional<ParsedVariable> getVariable(const std::string& variable_name) {
    // Στη λίστα με μεταβλητές της κλάσεις βρες τη μεταβλητή με όνομα variable_name
    const auto it = std::find_if(variables.begin(),
                                 variables.end(),
                                 [&variable_name](const ParsedVariable& var) {
                                   return var.name() == variable_name;
                                 });
    if (it == variables.end()) {
      return std::nullopt;
    }
    return *it;
  }

  std::optional<ParsedMethod> getMethod(const std::string& method_name) {
    auto it = std::find_if(methods.begin(),
                           methods.end(),
                           [&method_name](const ParsedMethod& var) {
                             return var.name() == method_name;
                           });
    if (it == methods.end()) {
      return std::nullopt;
    }
    return *it;
  }
};

class ParserHelper {
public:
  // Λίστα για την αναγνώριση κλάσης στην οποία γίνεται δήλωση μεταβλητών/μεθόδων
  std::vector<std::string> in_class{};

  std::map<std::string, ParsedClass> parsed_classes_map{};

  void addClass(const std::string& class_name, const bool is_public) {
    // std::cout << "Added class: " << class_name << std::endl;
    parsed_classes_map.insert({class_name, {class_name, is_public}});
  }

  void addVariable(const std::string& var_name, const DataType data_type, const bool is_public) {
    const auto parsed_variable = ParsedVariable(var_name, data_type, is_public);

    if (const auto it = parsed_classes_map.find(in_class.back()); it != parsed_classes_map.end()) {
      it->second.addVariable(parsed_variable);
      // std::cout << "Added variable: " << it->first << "." << var_name << std::endl;
      return;
    }
    std::cerr << "Something went wrong when adding variable in class: " << in_class.back() << std::endl;
  }

  void addMethod(const std::string& method_name, const DataType data_type, const bool is_public) {
    const auto parsed_method = ParsedMethod(method_name, data_type, is_public);

    if (const auto it = parsed_classes_map.find(in_class.back()); it != parsed_classes_map.end()) {
      it->second.addMethod(parsed_method);
      // std::cout << "Added method: " << it->first << "." << method_name << std::endl;
      return;
    }
    std::cerr << "Something went wrong when adding variable in class: " << in_class.back() << std::endl;
  }


  std::optional<std::string> getClass(const std::string& class_name) {
    if (const auto it = parsed_classes_map.find(class_name); it != parsed_classes_map.end()) {
      return std::nullopt;
    }
    return "Could not find class: " + class_name;
  }

  std::optional<std::string> getVariable(const std::string& class_name, const std::string& variable_name) {
    const auto it = parsed_classes_map.find(class_name);
    if (it == parsed_classes_map.end()) {
      return "No class: " + class_name;
    }

    const auto ret = it->second.getVariable(variable_name);
    if (!ret) {
      return "No variable: " + variable_name + " in class : " + class_name;
    }

    if (!ret.value().isPublic()) {
      if (class_name != in_class.back()) {
        // Αν η μεταβλητή δεν είναι public και δε βρισκόμαστε μέσα στην κλάση δήλωσης της μεταβλητής
        return "Cannot access: " + variable_name + " of class: " + class_name + " because it's private";
      }
    }
    return std::nullopt;
  }


  std::optional<std::string> getMethod(const std::string& class_name, const std::string& method_name) {
    const auto it = parsed_classes_map.find(class_name);
    if (it == parsed_classes_map.end()) {
      return "No class: " + class_name;
    }

    const auto ret = it->second.getMethod(method_name);
    if (!ret) {
      return "No variable: " + method_name + " in class : " + class_name;
    }

    if (!ret.value().isPublic()) {
      if (class_name != in_class.back()) {
        return "Cannot access: " + method_name + " of class: " + class_name + " because it's private";
      }
    }
    return std::nullopt;
  }
};

#endif