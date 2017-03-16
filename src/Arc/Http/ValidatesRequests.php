<?php

namespace Arc\Http;

use Arc\Exceptions\ValidationException;

class ValidatesRequests
{
    protected $customRules = [];
    protected $customFieldNames = [];

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

        return 'You must select at least one of the following: ' . implode($fields, ',');
    }

    private function failValidation($errors)
    {
        throw (new ValidationException('The request did not pass validation.'))
            ->setErrors($errors);
    }

    public function validResponse($data, $haystack, $fieldName)
    {
        // If the key is not present in the data, we don't need to verify that it's valid so it passes
        if (!isset($data[$fieldName])) {
            return true;
        }

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
