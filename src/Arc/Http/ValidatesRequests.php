<?php

namespace Arc\Http;

use Arc\BasePlugin;

class ValidatesRequests
{
    private $plugin;
    private $customRules;
    private $customFieldNames;

    public function __construct(BasePlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function validate($data, $rules)
    {
        // Declare result array
        $errors = [];

        // Iterate through submitted form data
        foreach($rules as $formField => $datum) {

            // Iterate through matching rules
            foreach($rules[$formField] as $ruleString) {

                // Separate components of rule string
                $ruleComponents = explode(':', $ruleString);

                $ruleName = $ruleComponents[0];

                $ruleParameters = isset($ruleComponents[1]) ? $ruleComponents[1] : null;

                // First check if custom validation rule is defined
                if (isset($this->customRules[$ruleName])) {
                    $result = $this->customRules[$ruleName]($data, $formField, $ruleParameters);
                }
                else {
                    $result = $this->$ruleName($data, $formField, $ruleParameters);
                }

                // Record error if validation fails
                if (!empty($result)) {
                    $errors[] = [$formField => $result];
                }
            }
        }

        // If errors found, return json and die
        if (count($errors)) {
            $this->failValidation($errors);
        }
    }

    private function required($data, $fieldName)
    {
        if (empty($data[$fieldName])) {
            return 'The ' . $this->getFieldName($fieldName) . ' field is required';
        }
    }

    // Ensures one of the field and the extra given fields is submitted
    private function requiredWith($data, $fieldName, $parameters)
    {
        $fields = explode(',', $parameters);
        $fields[] = $fieldName;

        foreach($fields as $field) {
            if (!empty($data[$field])) {
                // Return early if any of the fields is found
                return;
            }
        }

        // Prepare list of field names for error message
        foreach($fields as $key => $field) {
            $fieldNames .= $this->getFieldName($field);

            // Comma separate values if not last item in loop
            if ($key != count($fields) - 1 ) {
                $fieldNames .= ', ';
            }
        }

        return 'You must select at least one of the following: ' . $fieldNames;
    }

    private function failValidation($errors)
    {
        wp_send_json([
            'success' => false,
            'messages' => $errors
        ]);
    }

    public function validResponse($data, $haystack, $fieldName)
    {
        return (!in_array($data[$fieldName], $haystack) ? 'Must be a valid ' . $fieldName : false);
    }

    private function getFieldName($field)
    {
        if (isset($this->customFieldNames[$field])) {
            return $this->customFieldNames[$field];
        }

        return ucfirst(str_replace($field, '_', ' '));
    }

    /**
     * Add a custom validation rule
     **/
    public function extend($ruleName, $callback)
    {
        $this->customRules[$ruleName] = $callback;
    }

    /**
     * Add a custom field name mapping
     **/
    public function setCustomFieldName($field, $name)
    {
        $this->customFieldNames[$field] = $name;
    }
}
