<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

class PhoneAllowedCountries implements Rule
{
    protected array $allowed;

    protected string $messageKey = 'validation.phone_allowed_countries';

    public function __construct(array $allowed = ['CA', 'GB'])
    {
        $this->allowed = array_map('strtoupper', $allowed);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true; // allow nullable
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        foreach ($this->allowed as $region) {
            try {
                // Attempt to parse using region's defaults; libphonenumber will handle +<code> formats as well
                $proto = $phoneUtil->parse($value, $region);
                if ($phoneUtil->isValidNumberForRegion($proto, $region)) {
                    return true;
                }
            } catch (NumberParseException $e) {
                continue;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        // Fallback to a human readable message if translation key is not available
        $countries = implode(', ', $this->allowed);
        $translation = trans($this->messageKey, ['countries' => $countries]);
        if ($translation === $this->messageKey) {
            return "The :attribute must be a valid phone number from: {$countries}.";
        }
        return $translation;
    }
}
