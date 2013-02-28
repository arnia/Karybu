<?php
// florin, 9/27/12 3:45 PM 
abstract class Discount
{
    const
        DISCOUNT_TYPE_FIXED_AMOUNT = 'fixed_amount',
        DISCOUNT_TYPE_PERCENTAGE = 'percentage',
        PHASE_BEFORE_VAT = 'pre_taxes',
        PHASE_AFTER_VAT = 'post_taxes';

    private
        $value,
        $discountAmount,
        $minValueForDiscount,
        $VATPercent,
        $calculateBeforeApplyingVAT,
        $currency;

    /**
     * Child classes must provide a name
     * @return mixed
     */
    abstract public function getName();
    /**
     * Child classes must provide a description
     * @return mixed
     */
    abstract public function getDescription();
    /**
     * Child classes must provide a validation mecanism
     * @return mixed
     */
    abstract protected function validate($value, $discountValue);
    /**
     * Child classes must provide the discount value calculus
     * @return mixed
     */
    abstract protected function calculate($value, $discountValue);

    /**
     * Attributes parameters to private properties using setters and validates
     *
     * @param $totalValue
     * @param $discountValue
     * @param null $minValueForDiscount
     * @param int $VATPercent
     * @param bool $calculateBeforeApplyingVAT
     * @param null $currency
     */
    public function __construct($totalValue, $discountValue, $minValueForDiscount=null, $VATPercent=0, $calculateBeforeApplyingVAT=false, $currency=null)
    {
        $this
            ->setTotalValue($totalValue)
            ->setDiscountAmount($discountValue)
            ->setMinValueForDiscount($minValueForDiscount)
            ->setVATPercent($VATPercent)
            ->setCalculateBeforeVAT($calculateBeforeApplyingVAT)
            ->setCurrency($currency)
            ->validate($this->getTotalValue(), $this->getDiscountAmount());
    }

    /**
     * Calculates total minus discount
     * @return mixed
     */
    public function getValueDiscounted()
    {
        if ($this->getMinValueForDiscount() > $this->getTotalValue()) {
            return $this->getTotalValue();
        }
        return $this->getTotalValue() - $this->getReductionValue();
    }

    /**
     * Calculates discount (reduction) value
     * @return int|mixed
     */
    public function getReductionValue()
    {
        $minValueForDiscount = $this->getMinValueForDiscount();
        $totalValue = $this->getTotalValue();
        $calculateBeforeApplyingVAT = $this->calculateBeforeApplyingVAT();
        $valueWithoutVAT = $this->getValueWithoutVAT();
        $discountAmount = $this->getDiscountAmount();
        if ($minValueForDiscount > $totalValue) return 0;
        return $this->calculate(( $calculateBeforeApplyingVAT == false ? $totalValue : $valueWithoutVAT ), $discountAmount);
    }

    /**
     * Removes VAT from value
     * @return float
     */
    public function getValueWithoutVAT()
    {
        return $this->getTotalValue() / ( 1 + $this->getVATPercent() / 100);
    }

    //region Getters/setters
    public function setDiscountAmount($amount)
    {
        $this->discountAmount = $amount;
        return $this;
    }
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    public function setTotalValue($value)
    {
        $this->value = $value;
        return $this;
    }
    public function getTotalValue()
    {
        return $this->value;
    }

    public function setVATPercent($VATPercent)
    {
        if ($VATPercent < 0 || $VATPercent > 99.9) {
            throw new ShopException("Invalit VAT '$VATPercent'");
        }
        $this->VATPercent = $VATPercent;
        return $this;
    }
    public function getVATPercent()
    {
        return $this->VATPercent;
    }

    public function setCalculateBeforeVAT($calculateBeforeApplyingVAT)
    {
        if ($calculateBeforeApplyingVAT === self::PHASE_AFTER_VAT || $calculateBeforeApplyingVAT === self::PHASE_BEFORE_VAT) {
            $this->calculateBeforeApplyingVAT = self::PHASE_AFTER_VAT ? false : true;
        }
        elseif (is_bool($calculateBeforeApplyingVAT)) {
            $this->calculateBeforeApplyingVAT = $calculateBeforeApplyingVAT;
        }
        else throw new ShopException('Invalid setting');
        return $this;
    }
    public function calculateBeforeApplyingVAT()
    {
        return $this->calculateBeforeApplyingVAT;
    }

    public function setMinValueForDiscount($minValueForDiscount)
    {
        $this->minValueForDiscount = $minValueForDiscount;
        return $this;
    }
    public function getMinValueForDiscount()
    {
        return $this->minValueForDiscount;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
    public function getCurrency()
    {
        return $this->currency;
    }
    //endregion

}



class FixedAmountDiscount extends Discount
{

    /**
     * This is expanded from the abstract class in order to set the discount name
     * @return string
     */
    public function getName()
    {
        return "Fixed Amount Discount";
    }

    /**
     * Discount description
     * @return string
     */
    public function getDescription()
    {
        return "You get {$this->getDiscountAmount()}{$this->getCurrency()} discount when your cart value steps over {$this->getMinValueForDiscount()}{$this->getCurrency()}";
    }

    /**
     * Validates the discount
     * @param $value
     * @param $discountValue
     * @throws ShopException
     */
    protected function validate($value, $discountValue)
    {
        if ($value < $discountValue) {
            throw new ShopException("{$this->getTotalValue()} should be bigger than the fix amount discount value {$this->getDiscountAmount()}");
        }
    }

    /**
     * Effective discount calculus
     * @param $value
     * @param $discountValue
     * @return mixed
     */
    protected function calculate($value, $discountValue)
    {
        return $discountValue;
    }

}



class PercentageDiscount extends Discount
{
    /**
     * This is expanded from the abstract class in order to set the discount name
     * @return string
     */
    public function getName()
    {
        return "Percentage Discount";
    }

    /**
     * Discount description
     * @return string
     */
    public function getDescription()
    {
        return "{$this->getDiscountAmount()}% of your total order gets discounted when you step over {$this->getMinValueForDiscount()}{$this->getCurrency()}";
    }


    /**
     * Validates the discount
     * @param $value
     * @param $discountValue
     * @throws ShopException
     */
    protected function validate($value, $discountValue)
    {
        if ($discountValue > 99.9 || $discountValue < 0.1 ) {
            throw new ShopException('Discount value should be between 1 and 99');
        }
    }

    /**
     * Effective discount calculus
     * @param $value
     * @param $discountValue
     * @return mixed
     */
    protected function calculate($value, $discountValue)
    {
        return $discountValue / 100 * $value;
    }
}