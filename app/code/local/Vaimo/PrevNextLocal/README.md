**Usage on category page:**
app/design/frontend/{package}/{theme}/template/catalog/product/list.phtml

<?php echo Mage::helper('prevnextlocal/category')->getScript($productCollection); ?>

Will include a JavaScript with all collection Ids and Javascript to store it to Local Storage. 

**Usage on product page**
app/design/frontend/{package}/{theme}/template/catalog/product/view.phtml

Demo:
<?php echo Mage::helper('prevnextlocal/product')->getScript($product->getId()); ?>
<?php echo Mage::helper('prevnextlocal/product')->getEventScript(); ?>
First row is the script. It is needed. Second row is an example. Write your own in your module.

Real use:
<?php echo $this->getLayout()->createBlock('prevnextlocal/list')->setSpread(5)->setProductId($_product->getId())->toHtml(); ?>
<?php echo $this->getLayout()->createBlock('prevnextlocal/detail')->toHtml(); ?>
This uses two widgets that are in the module.

**Usage**
This is what you get if you run:
<?php echo Mage::helper('prevnextlocal/product')->getEventScript(); ?>

<script type="text/javascript">
    function vaimoPrevNextLocalHaveDataHandler($eventData) {
        var $productsData = $eventData.detail;
        console.log("Got the product data");
        console.dir($productsData);
    }
    document.addEventListener("vaimoPrevNextLocalHaveData", vaimoPrevNextLocalHaveDataHandler, false);
    jQuery( document ).ready(function() {
        var $productId = localStorage.getItem("vaimo_prevnextlocal_product_id");
        $prevNextLocal.getProductData({"product_id":$productId, "spread":5 });
    });
</script>

You can write your own script and create your own widget in your module.

**Widgets in the module**
Prev Next Local | Image list 
    Show a list with images you can click. The middle product is framed.
    Click the middle image to visit its product page.
    Click any other product to reload the images and place that product in the middle.
    When redraw is finished: trigger an event and send away the middle product data. 

Prev Next Local | Image details
    Listen to event and update the title, short_description, image, SKU in a div box.

**Use widget in template file**
<?php echo $this->getLayout()->createBlock('prevnextlocal/list')->setSpread(5)->toHtml(); ?>
<?php echo $this->getLayout()->createBlock('prevnextlocal/detail')->toHtml(); ?>

Also add the css file to your layout:
app/design/frontend/{package}/{theme}/layout/local.xml
<catalog_product_view>
    <reference name="head">
        <action method="addItem">
            <type>skin_css</type>
            <name>css/vaimo/vaimo_prevnextlocal.css</name>
            <params/>
        </action>
    </reference>
</catalog_product_view>

**todo**
* Add a clean-up function that cleans up old data in the local storage.
* Star-widget.
	* Graphics: Find a png star, and a filled png star
	* Create a widget that handle starred products.
	* Store a JSON in local storage for starred products.
	* get an event that these productIds+containerIds can be decorated.
	* add a clickable star in the info widget.
	* When you mark/unmark then star in the info widget you get an event:
		* add/remove a product from the starred collection in the local storage
		* update the star on the collection widget image
		* update the star on the info widget
* Collection widget
	* Add a div on the upper right on each image. Used by star-widget
	* Add a bigger div to the upper right. Used by star-widget to show a star you can toggle to show the category collection or the starred collection.
	* Add a setting in the widget to fetch starred collection (if exist) OR ELSE category collection.
	* Add a method to update the collection with starred collection. Used by star-widget.
* Info-widget
	* Add a bigger div to the upper right. Used by star-widget to show a star you can toggle to star this product.
