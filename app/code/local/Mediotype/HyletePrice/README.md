# Mediotype/HyletePrice

## Overview
Instead of adding price attributes, the extant product attribute 'msrp' has been used as the value that will display across the website under the "Retail Price" heading.

This allows for simple control of displayed prices, including by customer group.

"Retail Price" is populated by the "msrp" product price attribute, as noted above. "Hylete Price", where "Hylete" is configurable via customer groups, defaults to the normal Magento price, or the group price, if applicable.

To configure the "Hylete Price" verbiage based on customer groups, navigate to the adminhtml, then Customers > Customer Groups. Choose a group or create a new one. The form has a new input, which will be displayed instead of "Hylete" wherever a Hylete Price is displayed. Note that as indicated on the form, "Price" is automatically suffixed to this value!

The "NOT LOGGED IN" user group is the "default" label; if a customer group doesn't have a label, then the label associated with Customer Group 0 will be used.

Categories now have an attribute that lets our module know that a category is a clearance page, and will show the Hylete Price Group label for "NOT LOGGED IN" as well.
