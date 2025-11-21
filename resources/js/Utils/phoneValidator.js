import { parsePhoneNumberFromString } from 'libphonenumber-js';

export function validatePhone(number) {
  if (!number) {
    return { isValid: false, country: null, formatted: null };
  }

  try {
    const phoneNumber = parsePhoneNumberFromString(number);
    if (!phoneNumber) {
      return { isValid: false, country: null, formatted: null };
    }
    const country = phoneNumber.country || null;
    const isValid = phoneNumber.isValid() && (country === 'GB' || country === 'CA');
    const formatted = isValid ? phoneNumber.format('E.164') : null;
    return { isValid, country, formatted };
  } catch (err) {
    return { isValid: false, country: null, formatted: null };
  }
}

export default validatePhone;
