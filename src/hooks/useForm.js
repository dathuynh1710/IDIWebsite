import { useState, useCallback } from 'react'

/**
 * useForm — Generic form state management with validation.
 *
 * @param {object} initialValues - Initial field values
 * @param {object} validationRules - { fieldName: (value) => { valid, message } }
 * @returns {{ values, errors, touched, handleChange, handleBlur, handleSubmit, isSubmitting, reset }}
 *
 * Usage:
 *   const { values, errors, handleChange, handleSubmit } = useForm(
 *     { email: '', company: '' },
 *     { email: (v) => validate(v, isRequired, isEmail) }
 *   )
 */
export function useForm(initialValues = {}, validationRules = {}) {
  const [values, setValues]           = useState(initialValues)
  const [errors, setErrors]           = useState({})
  const [touched, setTouched]         = useState({})
  const [isSubmitting, setIsSubmitting] = useState(false)

  const validateField = useCallback((name, value) => {
    const rule = validationRules[name]
    if (!rule) return ''
    const result = rule(value)
    return result.valid ? '' : result.message
  }, [validationRules])

  const handleChange = useCallback((e) => {
    const { name, value, type, checked } = e.target
    const fieldValue = type === 'checkbox' ? checked : value
    setValues(prev => ({ ...prev, [name]: fieldValue }))
    // Clear error on change if field has been touched
    if (touched[name]) {
      setErrors(prev => ({ ...prev, [name]: validateField(name, fieldValue) }))
    }
  }, [touched, validateField])

  const handleBlur = useCallback((e) => {
    const { name, value } = e.target
    setTouched(prev => ({ ...prev, [name]: true }))
    setErrors(prev => ({ ...prev, [name]: validateField(name, value) }))
  }, [validateField])

  const validateAll = useCallback(() => {
    const newErrors = {}
    let isValid = true
    Object.keys(validationRules).forEach(name => {
      const errorMsg = validateField(name, values[name])
      if (errorMsg) { newErrors[name] = errorMsg; isValid = false }
    })
    setErrors(newErrors)
    setTouched(Object.keys(validationRules).reduce((acc, k) => ({ ...acc, [k]: true }), {}))
    return isValid
  }, [values, validationRules, validateField])

  const handleSubmit = useCallback((onSubmit) => async (e) => {
    e.preventDefault()
    if (!validateAll()) return
    setIsSubmitting(true)
    try {
      await onSubmit(values)
    } finally {
      setIsSubmitting(false)
    }
  }, [values, validateAll])

  const reset = useCallback(() => {
    setValues(initialValues)
    setErrors({})
    setTouched({})
    setIsSubmitting(false)
  }, [initialValues])

  return { values, errors, touched, handleChange, handleBlur, handleSubmit, isSubmitting, reset }
}
