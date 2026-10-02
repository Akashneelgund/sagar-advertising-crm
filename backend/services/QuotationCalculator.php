<?php
/**
 * Sagar Advertising CRM - Quotation Calculation Engine
 * Handles precision commercial math for commission markup/margins, line items, and GST taxes.
 */

declare(strict_types=1);

class QuotationCalculator {
    /**
     * Compute single line item values
     *
     * @param float $actualPrice Base cost per unit
     * @param float $commissionRate Commission percentage (e.g., 15.0)
     * @param float $quantity Quantity units
     * @param string $mode 'markup' (Actual + Commission) or 'margin' (Commission included in Selling Price)
     * @return array [actual_price, commission_rate, commission_amount, selling_price, total_amount, total_commission]
     */
    public static function calculateLineItem(
        float $actualPrice,
        float $commissionRate,
        float $quantity,
        string $mode = 'markup'
    ): array {
        $actualPrice = max(0.0, $actualPrice);
        $commissionRate = max(0.0, $commissionRate);
        $quantity = max(0.0001, $quantity);

        if ($mode === 'margin' && $commissionRate < 100.0) {
            // Margin mode: Selling price contains commission
            $sellingPrice = $actualPrice / (1.0 - ($commissionRate / 100.0));
            $commissionAmount = $sellingPrice - $actualPrice;
        } else {
            // Default markup mode: Actual + (Actual * Commission%)
            $commissionAmount = ($actualPrice * $commissionRate) / 100.0;
            $sellingPrice = $actualPrice + $commissionAmount;
        }

        $lineTotal = round($sellingPrice * $quantity, 2);
        $totalCommission = round($commissionAmount * $quantity, 2);

        return [
            'actual_price'      => round($actualPrice, 2),
            'commission_rate'   => round($commissionRate, 2),
            'commission_amount' => round($commissionAmount, 2),
            'selling_price'     => round($sellingPrice, 2),
            'total_amount'      => $lineTotal,
            'total_commission'  => $totalCommission,
            'total_actual_cost' => round($actualPrice * $quantity, 2)
        ];
    }

    /**
     * Compute entire quotation financial totals
     *
     * @param array $items Array of line items
     * @param float $discount Discount value
     * @param float $gstRate GST % (default 18%)
     * @param float $transport Transportation charges
     * @param float $installation Installation charges
     * @param string $mode 'markup' or 'margin'
     * @return array Summary totals
     */
    public static function calculateQuotationTotals(
        array $items,
        float $discount = 0.0,
        float $gstRate = 18.0,
        float $transport = 0.0,
        float $installation = 0.0,
        string $mode = 'markup'
    ): array {
        $subtotal = 0.0;
        $totalCommission = 0.0;
        $totalActualCost = 0.0;
        $calculatedItems = [];

        foreach ($items as $idx => $item) {
            $act = (float)($item['actual_price'] ?? 0);
            $commRate = (float)($item['commission_rate'] ?? 15);
            $qty = (float)($item['quantity'] ?? 1);

            $calc = self::calculateLineItem($act, $commRate, $qty, $mode);
            
            $calculatedItems[] = array_merge($item, $calc, ['sort_order' => $idx]);

            $subtotal += $calc['total_amount'];
            $totalCommission += $calc['total_commission'];
            $totalActualCost += $calc['total_actual_cost'];
        }

        $subtotal = round($subtotal, 2);
        $discount = max(0.0, min($discount, $subtotal));
        $transport = max(0.0, $transport);
        $installation = max(0.0, $installation);

        // Taxable amount includes extra operational charges after discount
        $taxableAmount = round($subtotal - $discount + $transport + $installation, 2);

        $gstRate = max(0.0, $gstRate);
        $gstAmount = round(($taxableAmount * $gstRate) / 100.0, 2);

        $rawGrandTotal = $taxableAmount + $gstAmount;
        $roundedGrandTotal = round($rawGrandTotal);
        $roundOff = round($roundedGrandTotal - $rawGrandTotal, 2);

        $marginPercent = ($subtotal > 0) ? round(($totalCommission / $subtotal) * 100, 2) : 0.0;

        return [
            'items'                  => $calculatedItems,
            'subtotal'               => $subtotal,
            'total_actual_cost'      => round($totalActualCost, 2),
            'total_commission'       => round($totalCommission, 2),
            'margin_percent'         => $marginPercent,
            'discount_amount'        => round($discount, 2),
            'transportation_charges' => round($transport, 2),
            'installation_charges'   => round($installation, 2),
            'taxable_amount'         => $taxableAmount,
            'gst_rate'               => round($gstRate, 2),
            'gst_amount'             => $gstAmount,
            'round_off'              => $roundOff,
            'grand_total'            => $roundedGrandTotal
        ];
    }
}
