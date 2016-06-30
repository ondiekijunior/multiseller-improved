<?php

class MsValidator extends Model {

	private $errors = array();

	public function validate(array $input, array $ruleset)
	{
		foreach ($ruleset as $key => $item) {
			$rules = explode('|', $item['rule']);

			foreach ($rules as $rule) {
				$method = null;
				$param = null;
				$error_message = null;

				if (strstr($rule, ',') !== false) {
					$rule   = explode(',', $rule);
					$method = 'validate_'.$rule[0];
					$param  = $rule[1];
					$rule   = $rule[0];
				} else {
					$method = 'validate_'.$rule;
					if (isset($item['error_message'])) {
						$error_message = $item['error_message'];
					}
				}

				if (is_callable(array($this, $method))) {
					$result = $this->$method($input, $param, $error_message);
					if (is_array($result)) {
						$this->errors[] = $result;
					}
				} else {
					throw new Exception("Validator method '$method' does not exist.");
				}
			}
		}
		return (count($this->errors) > 0) ? false : true;
	}

	public function get_errors()
	{
		$response = array();

		foreach ($this->errors as $e) {
			switch ($e['rule']) {
				case 'validate_required' :
					$default_message = sprintf($this->language->get('ms_validate_required'), $e['field']);
					break;
				case 'validate_alpha_numeric':
					$default_message = sprintf($this->language->get('ms_validate_alpha_numeric'), $e['field']);
					break;
				case 'validate_max_len':
					$default_message = sprintf($this->language->get('ms_validate_max_len'), $e['field'], $e['param']);
					break;
				case 'validate_min_len':
					$default_message = sprintf($this->language->get('ms_validate_min_len'), $e['field'], $e['param']);
					break;
				case 'validate_phone_number':
					$default_message = sprintf($this->language->get('ms_validate_phone_number'), $e['field']);
					break;
				case 'validate_valid_url':
					$default_message = sprintf($this->language->get('ms_validate_valid_url'), $e['field']);
					break;
				default:
					$default_message = sprintf($this->language->get('ms_validate_default'), $e['field']);
			}
			$response[] = (isset($e['error_message'])) ? $e['error_message'] : $default_message;
		}
		$this->errors = [];
		return $response;
	}

	protected function validate_required($input, $param = null, $message = null)
	{
		if (isset($input['value']) && ($input['value'] === false || $input['value'] === 0 || $input['value'] === 0.0 || $input['value'] === '0' || !empty($input['value']))) {
			return;
		}

		return array(
			'field' => $input['name'],
			'value' => $input['value'],
			'rule' => __FUNCTION__,
			'param' => $param,
			'error_message' => $message
		);
	}

	protected function validate_alpha_numeric($input, $param = null, $message = null)
	{
		if (!isset($input['value']) || empty($input['value'])) {
			return;
		}

		if (!preg_match('/^([A-Za-z0-9])+$/i', $input['value']) !== false) {
			return array(
				'field' => $input['name'],
				'value' => $input['value'],
				'rule' => __FUNCTION__,
				'param' => $param,
				'error_message' => $message
			);
		}
	}

	protected function validate_max_len($input, $param = null, $message = null)
	{
		if (!isset($input['value'])) {
			return;
		}

		if (function_exists('mb_strlen')) {
			if (mb_strlen($input['value']) <= (int) $param) {
				return;
			}
		} else {
			if (strlen($input['value']) <= (int) $param) {
				return;
			}
		}

		return array(
			'field' => $input['name'],
			'value' => $input['value'],
			'rule' => __FUNCTION__,
			'param' => $param,
			'error_message' => $message
		);
	}

	protected function validate_min_len($input, $param = null, $message = null)
	{
		if (!isset($input['value'])) {
			return;
		}

		if (function_exists('mb_strlen')) {
			if (mb_strlen($input['value']) >= (int) $param) {
				return;
			}
		} else {
			if (strlen($input['value']) >= (int) $param) {
				return;
			}
		}

		return array(
			'field' => $input['name'],
			'value' => $input['value'],
			'rule' => __FUNCTION__,
			'param' => $param,
			'error_message' => $message
		);
	}

	protected function validate_phone_number($input, $param = null, $message = null)
	{
		if (!isset($input['value']) || empty($input['value'])) {
			return;
		}

		$regex = '/^[0-9]{5,25}$/';
		if (!preg_match($regex, $input['value'])) {
			return array(
				'field' => $input['name'],
				'value' => $input['value'],
				'rule' => __FUNCTION__,
				'param' => $param,
				'error_message' => $message
			);
		}
	}

	protected function validate_valid_url($input, $param = null, $message = null)
	{
		if (!isset($input['value']) || empty($input['value'])) {
			return;
		}

		if (!preg_match('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $input['value'])) {
			return array(
				'field' => $input['name'],
				'value' => $input['value'],
				'rule' => __FUNCTION__,
				'param' => $param,
				'error_message' => $message
			);
		}
	}
}
