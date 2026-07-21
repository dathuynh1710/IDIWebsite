/**
 * validators.js — Pure validation functions for form fields.
 * Each validator returns: { valid: boolean, message: string }
 * Used by useForm hook to validate fields on change/blur/submit.
 */

/**
 * Checks that a value is not empty.
 */
export function isRequired(value, fieldName = 'This field') {
  const trimmed = String(value ?? '').trim()
  return {
    valid: trimmed.length > 0,
    message: `${fieldName} is required.`,
  }
}

/**
 * Validates email format.
 */
export function isEmail(value) {
  const EMAIL_RE = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/
  return {
    valid: EMAIL_RE.test(String(value).trim()),
    message: 'Please enter a valid email address.',
  }
}

/**
 * Validates minimum string length.
 */
export function minLength(value, min = 2, fieldName = 'This field') {
  const trimmed = String(value ?? '').trim()
  return {
    valid: trimmed.length >= min,
    message: `${fieldName} must be at least ${min} characters.`,
  }
}

/**
 * Validates maximum string length.
 */
export function maxLength(value, max = 500, fieldName = 'This field') {
  const trimmed = String(value ?? '').trim()
  return {
    valid: trimmed.length <= max,
    message: `${fieldName} must be ${max} characters or fewer.`,
  }
}

/**
 * Validates a phone number (international format).
 * Accepts: +1234567890, +1 (234) 567-8900, etc.
 */
export function isPhone(value) {
  const PHONE_RE = /^\+?[\d\s\-().]{7,20}$/
  return {
    valid: PHONE_RE.test(String(value).trim()),
    message: 'Please enter a valid phone number.',
  }
}

/**
 * Validates a positive number (for MT/volume fields).
 */
export function isPositiveNumber(value, fieldName = 'Value') {
  const num = parseFloat(value)
  return {
    valid: !isNaN(num) && num > 0,
    message: `${fieldName} must be a positive number.`,
  }
}

/**
 * Composes multiple validators — stops at first failure.
 * @param {*} value - Field value
 * @param  {...Function} validators - Validator functions
 * @returns {{ valid: boolean, message: string }}
 *
 * Usage:
 *   validate(email, isRequired, isEmail)
 *   validate(volume, isRequired, isPositiveNumber)
 */
export function validate(value, ...validators) {
  for (const validator of validators) {
    const result = validator(value)
    if (!result.valid) return result
  }
  return { valid: true, message: '' }
}
