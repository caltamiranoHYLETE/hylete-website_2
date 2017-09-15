# README #

Magento module, used for getting data about products in the last viewed collection.
On the product page, you can show data about the previously or next coming products with image and information.
The module have no frontend, you as a frontend developer can use data and present it as you like.

### What is this repository for? ###

* Get data about prev/next product in the latest collection.
* Developed on Magento EE 1.12.1.0 Using no special Magento functions, probably work in more versions.

### How do I get set up? ###

* Install from Server portal or ass to your Aja project
* Admin config: Admin >> System >> Configuration >> Vaimo modules >> Previous/Next Local
* Dependencies: Magento, jQuery, a browser with local storage (Almost every browser)

### Usage on category page: ###


```
#!PHP

<?php $_productCollection = $this->getLoadedProductCollection(); ?>
<?php echo Mage::helper('prevnextlocal/category')->getScript($_productCollection); ?>
```


### Usage on product page ###


```
#!PHP

<?php echo Mage::helper('prevnextlocal/product')->getScript($_product->getId()); ?>
<?php echo Mage::helper('prevnextlocal/product')->getEventScript(); ?>
<div class="prev_next">
    <a href="#" id="vaimo_prevnextlocal_prev" class="previous"><?php echo $this->__('Go to previous product');?></a>
    <a href="#" id="vaimo_prevnextlocal_next" class="next"><?php echo $this->__('Go to next product');?></a>
    <a href="#" id="vaimo_prevnextlocal_back" class="back"><?php echo $this->__('Back to previous category view');?></a>
</div>

```

### Functions ###


```
#!javascript

getProductData({'product':12949 });
```

Triggers an event when it has your data.

You must provide with an event listener to get the data and act on it.
You can use the event script provided or write your own.
<?php echo Mage::helper('prevnextlocal/product')->getEventScript(); ?>

### Contribution guidelines ###

* keep it simple and independent,
* do not change the interfaces, 
* do not introduce exception cases

### Who do I talk to? ###

* Peter Lembke, peter.lembke@vaimo.com
* Created on Hackaton IV - 2015-01-16