<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Hylete
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 */

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->startSetup();
$content = <<<EOF
<div class="content-header">
<h1>FREQUENTLY ASKED QUESTIONS</h1>
</div>
<div class="std">
<div class="section">
<h2>returns/exchanges &amp; warranty</h2>
<div class="toggle">
<h3>How do I return an item?</h3>
<div class="content-data">
<p>Click here to complete your return form. Once completed you will be prompted to print your prepaid shipping label.</p>
<p>Click here to complete an international return form. A customer service representative will contact you within 1-3 business days with return instructions. International orders are not eligible for free shipping at this time.</p>
</div>
</div>
<div class="toggle">
<h3>How do I receive a full refund?</h3>
<div class="content-data">
<p><span>Return your item(s) in new condition with all the original tags attached within 60 days for a full refund. If you return an item(s) without the original tags​ attached a $10 restocking fee will be deducted from your refund. If an item(s) is used or past 60 days, it is not eligible for return.</span></p>
</div>
</div>
<div class="toggle">
<h3>How do I exchange an item?</h3>
<div class="content-data">
<p><span>If you would like to exchange an item for a new size, color, style or product, return the item(s) for a refund and place a new order on HYLETE.com. We recommend placing your exchange order prior to sending back the item(s) as quantities on some product is limited. Your account will be refunded when the item(s) have been received and processed by our warehouse.</span></p>
</div>
</div>
<div class="toggle">
<h3>How long does the refund process take?</h3>
<div class="content-data">
<p><span>Once your order has been received in our warehouse, please allow 2-3 business days for your return to be processed. Once your return has been processed your refund will be issued immediately. Refunds typically take 2-3 days to clear your account depending on your bank.</span></p>
</div>
</div>
<div class="toggle">
<h3>I have a defective product and I would like it replaced. What is your warranty policy?</h3>
<div class="content-data">
<p><span>We take pride in all of our products. If your product is damaged due to a manufacturer defect we will happily replace it for you. Please email a photo of the defective product to customerservice@hylete.com and a team member will follow up with you.</span></p>
</div>
</div>
</div>
<div class="section">
<h2>sizing</h2>
<div class="toggle">
<h3>Where can I find size information?</h3>
<div class="content-data">
<p>If you click on any individual product on the website, you can find the sizing information for that product under the 'SIZE INFO' tab. This tab can be found under the 'select size' option.</p>
</div>
</div>
<div class="toggle">
<h3>How do I determine my size in the men's shorts?</h3>
<div class="content-data">
<p>The best way to find your size in our shorts is by referencing your true jean size (i.e. 30"- small, 32"- medium, 34"- large, 36"- x-large, 38"-40"- xx-large). Sizing information for each product can be found under the 'SIZE INFO' tab on the products detail page. If you are between sizes, purchase the bigger size to be safe.</p>
</div>
</div>
</div>
<div class="section">
<h2>shipping</h2>
<div class="toggle">
<h3>How do I receive free shipping within the U.S.?</h3>
<div class="content-data">
<p>Free shipping and free returns within the U.S. are available for customers with a powered by HYLETE account or HYLETE team account. Click here to create your powered by HYLETE account.</p>
</div>
</div>
</div>
<div class="section">
<h2>order tracking</h2>
<div class="toggle">
<h3>How do I track my order?</h3>
<div class="content-data">
<p>After your order ships, you will receive an email confirmation with tracking information. Follow the steps within the email to track your order. If you have not received an email with tracking information, please email customerservice@hylete.com and one of our team members will gladly assist you.</p>
</div>
</div>
<div class="toggle">
<h3>Can I change my order after it's been placed?</h3>
<div class="content-data">
<p>We start processing your order just minutes after it is placed. We cannot guarantee adjustments but we can certainly do our very best to try! Please contact us immediately at customerservice@hylete.com.</p>
</div>
</div>
</div>
<div class="section">
<h2>reseller account</h2>
<div class="toggle">
<h3>I am interested in wholesale pricing, reseller options, and/or sponsorship for my upcoming event.</h3>
<div class="content-data">
<p>Click here to learn more about our "powered by HYLETE" program. One of our team members will get in touch with you within the next 3-5 business days to discuss specifics.</p>
</div>
</div>
<div class="toggle">
<h3>I have a reseller account but my log in information does not work.</h3>
<div class="content-data">
<p>All reseller orders must be completed through reseller.HYLETE.com. If you do not have an active account on reseller.HYLETE.com or you would like more information about becoming a reseller, please contact us at pbHsales@HYLETE.com.</p>
</div>
</div>
</div>
<div class="section">
<h2>misc. product questions</h2>
<div class="toggle">
<h3>What if a product I want to purchase is out of stock?</h3>
<div class="content-data">
<p>If you click the 'notify me' on any out of stock product and enter your email address, you will automatically receive an email from us when it returns to stock. For tentative restock dates, please email customerservice@hylete.com.</p>
</div>
</div>
</div>
<div class="section">
<h2>international orders</h2>
<div class="toggle">
<h3>Are international orders eligible for free shipping?</h3>
<div class="content-data" style="display: none;">
<p>International customers are not eligible for free shipping. International shipping charges will be calculated by the customers preferred carrier at checkout.</p>
</div>
</div>
<div class="toggle">
<h3>Will I be charged Duties/Customs charges?</h3>
<div class="content-data" style="display: none;">
<p>All international/Canadian shipments may be subject to import charges. These charges vary depending on the retail value of your items and the country you are shipping to.</p>
</div>
</div>
<div class="toggle">
<h3>How do I return an international order?</h3>
<div class="content-data" style="display: none;">
<p>Click here to complete an international return form. A customer service representative will contact you within 1-3 business days with return instructions. International orders are not eligible for free shipping at this time.</p>
</div>
</div>
</div>
<div class="section">
<h2>promotional codes</h2>
<div class="toggle">
<h3>Why isn't my promotional code working?</h3>
<div class="content-data" style="display: none;">
<p>If your promo code is not working it is most likely because the promo code has expired. Promotional codes often require you to be logged in. Some codes also require a specific product mix to be in your cart to be activated. Please check the promotional code disclaimer at checkout to reference all non-discountable products. If you are still having trouble with a promotional code, please contact us at customerservice@hylete.com and include the promotional code and a screenshot of your shopping cart, if possible.</p>
</div>
</div>
<div class="toggle">
<h3>I placed my order and forgot to enter my discount code. What do I do?</h3>
<div class="content-data" style="display: none;">
<p>Please contact us within a reasonable amount of time after your order has been placed and we may be able to retroactively apply the discount code to your order, assuming it is a qualifying code. Keep in mind that our policy allows for one discount code to be applied per order.</p>
</div>
</div>
</div>
</div>
EOF;

try {
    Mage::getModel('cms/page')
            ->setTitle('Frequently asked questions')
            ->setRootTemplate('one_column')
            ->setIdentifier('faq')
            ->setContent($content)
            ->setStores(array(0))
            ->save();
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();