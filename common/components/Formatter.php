<?php

namespace common\components;

use NumberFormatter;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

class Formatter extends \yii\i18n\Formatter
{
    private $_intlLoaded = false;

    public function init()
    {
        $this->_intlLoaded = extension_loaded('intl');
        parent::init();

        if (Yii::$app->language == Config::LANGUAGE_CYRILLIC)
            $this->locale = 'uz_Cyrl_UZ';
    }

    public function asCurrency($value, $currency = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $currency = $currency ?: $this->currencyCode;
            // currency code must be set before fraction digits
            // http://php.net/manual/en/numberformatter.formatcurrency.php#114376
            if ($currency && !isset($textOptions[NumberFormatter::CURRENCY_CODE])) {
                $textOptions[NumberFormatter::CURRENCY_CODE] = $currency;
            }
            $formatter = $this->createNumberFormatter(NumberFormatter::CURRENCY, null, $options, $textOptions);
            if ($currency == 'UZS' && $formatter->getLocale() == 'uz') {
                $formatter->setPattern('#,##0 ¤');
            }
            if ($currency === null) {
                $result = $formatter->format($value);
            } else {
                $result = $formatter->formatCurrency($value, $currency);
            }
            if ($result === false) {
                throw new InvalidParamException('Formatting currency value failed: ' . $formatter->getErrorCode() . ' ' . $formatter->getErrorMessage());
            }

            return $result;
        }

        if ($currency === null) {
            if ($this->currencyCode === null) {
                throw new InvalidConfigException('The default currency code for the formatter is not defined and the php intl extension is not installed which could take the default currency from the locale.');
            }
            $currency = $this->currencyCode;
        }

        return $currency . ' ' . $this->asDecimal($value, 2, $options, $textOptions);
    }

}
