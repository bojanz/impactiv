<?php

namespace Drupal\impactiv\Plugin\Commerce\TaxType;

use Drupal\commerce_order\Adjustment;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Calculator;
use Drupal\commerce_tax\Plugin\Commerce\TaxType\TaxTypeBase;

/**
 * Provides the Hotel tax type.
 *
 * @CommerceTaxType(
 *   id = "hotel",
 *   label = "Hotel",
 * )
 */
class Hotel extends TaxTypeBase {

  /**
   * {@inheritdoc}
   */
  public function applies(OrderInterface $order) {
    // This tax type always applies, because it handles taxes for every store.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function apply(OrderInterface $order) {
    $store = $order->getStore();
    $tax_amount = NULL;
    $percentage = NULL;
    // We assume that the store has a field_tax_amount commerce_price field
    // that stores the fixed amount to apply, and a field_tax_percentage
    // which stores a percentage (15, 20, etc) to apply.
    if (!$store->get('field_tax_amount')->isEmpty()) {
      $tax_amount = $store->get('tax_amount')->first()->toPrice();
    }
    elseif (!$store->get('field_tax_percentage')->isEmpty()) {
      $percentage = Calculator::divide($store->get('tax_percentage')->value, 100);
    }

    foreach ($order->getItems() as $order_item) {
      if ($percentage) {
        $tax_amount = $order_item->getAdjustedUnitPrice()->multiply($percentage);
      }

      $order_item->addAdjustment(new Adjustment([
        'type' => 'tax',
        'label' => t('Hotel tax'),
        'amount' => $tax_amount,
        'percentage' => $percentage,
        'source_id' => $store->id(),
        // Hotel tax is not included in the displayed price.
        'included' => FALSE,
      ]));
    }
  }

}
