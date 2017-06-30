/**
 * Copyright © 2009-2011 Icommerce Nordic AB
 *
 * Icommerce reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Icommerce, except as provided by licence. A licence
 * under Icommerce's rights in the Program may be available directly from
 * Icommerce.
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
 * @category    Icommerce
 * @package     Icommerce_Enhancedgrid
 * @copyright   Copyright © 2009-2012 Icommerce Nordic AB
 */
function chooseWhatToRelateTo() {
  var productids = window.prompt("Enter the id's for products you'd like to relate the currently selected products to.\n"
                  +"For example: Suppose you selected X, Y and Z.  If you enter 'A,B' here, X will be\n"
                  +"related to A and B, Y will be related to A and B, etc.\n"
                  +"Separate multiple product ids by a comma as shown in the example above.", "<Enter product IDs (NOT SKUs!)>");
  if (productids == "" || productids == null) {
    return null
  }
  if (!window.confirm("Are you sure you'd like to one-way relate selected grid products to products ("+ productids +")")) {
    return null
  }
  return productids;
}
function chooseWhatToCrossSellTo() {
  var productids = window.prompt("Enter the id's for products you'd like to add as cross-sell to the currently selected products.\n"
                  +"For example: Suppose you selected X, Y and Z.  If you enter 'A,B' here, X will be\n"
                  +"cross-sold to A and B, Y will be cross-sold with A and with B, etc.\n"
                  +"Separate multiple product ids by a comma as shown in the example above.", "<Enter product IDs (NOT SKUs!)>");
  if (productids == "" || productids == null) {
    return null
  }
  if (!window.confirm("Are you sure you'd like to one-way cross-sell products ("+ productids +") to selected grid products?")) {
    return null
  }
  return productids;
}

function chooseWhatToUpSellTo() {
  var productids = window.prompt("Enter the id's for products you'd like to add as up-sells to the currently selected products.\n"
                  +"For example: Suppose you selected X, Y and Z.  If you enter 'A,B' here, A and B will be\n"
                  +"up-sells of X , A and B will be up-sells of Y, etc.\n"
                  +"Separate multiple product ids by a comma as shown in the example above.", "<Enter product IDs (NOT SKUs!)>");
  if (productids == "" || productids == null) {
    return null
  }
  if (!window.confirm("Are you sure you'd like add products ("+ productids +") to selected grid products up-sell?")) {
    return null
  }
  return productids;
}



function showSelectedImages(gridObj, checkedValues, imgTemplate) {
    var matchCounter = 0;
    gridObj.walkSelectedRows(function(ie){
      ie.getElementsBySelector('a').each(function(a) {
        if(a.id == "imageurl") {
          matchCounter++;
          a.innerHTML = imgTemplate.replace("{imgurl}", a.getAttribute('url'));
        }
      });
    });
  if(matchCounter == 0) {
    alert("Either there was no image column, or the image column could not be found");
  }
  return null;

}

function hideSelectedImages(gridObj, checkedValues) {
    var matchCounter = 0;
    gridObj.walkSelectedRows(function(ie){
      ie.getElementsBySelector('a').each(function(a) {
        if(a.id == "imageurl") {
          matchCounter++;
          a.innerHTML = "@";
        }
      });
    });
  if(matchCounter == 0) {
    alert("Either there was no image column, or the image column could not be found");
  }
  return null;

}

function openAllImages(gridObj, checkedValues) {
    gridObj.walkSelectedRows(function(ie){
      ie.getElementsBySelector('a').each(function(a) {
        if(a.id == "imageurl") {
          window.open(a.getAttribute('url'));
        }
      });
    }, 30);
  return null;

}

function openAll(gridObj, checkedValues) {
    gridObj.walkSelectedRows(function(ie){
      window.open(ie.id);
    }, 20);
  return null;

}