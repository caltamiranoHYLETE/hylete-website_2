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
 
 
//form tags to omit in NS6+:

var omitformtags=["input", "textarea", "select"]

omitformtags=omitformtags.join("|")

function disableselect(e){
if (omitformtags.indexOf(e.target.tagName.toLowerCase())==-1)
return false
}

function reEnable(){
return true
}

var originalHighlighting = false;

function disableHighlighting() {
  if (typeof document.onselectstart!="undefined") {
    originalHighlighting = document.onselectstart;
    document.onselectstart=new Function ("return false")
  } else{
    originalHighlighting = {
      down: document.onmousedown,
      up:   document.onmouseup
    }
    document.onmousedown=disableselect
    document.onmouseup=reEnable
  }
}

function enableHighlighting() {
  if (typeof document.onselectstart!="undefined") {
    document.onselectstart = originalHighlighting;
  } else{
    document.onmousedown=originalHighlighting.down;
    document.onmouseup=originalHighlighting.up;
  }
}

function keyWasPressed(e, targetKeyNum) {
    var keychar;
    var numcheck;
    
    if(window.event) // IE
    {
        keynum = e.keyCode;
    }
    else if(e.which) // Netscape/Firefox/Opera
    {
        keynum = e.which;
    }
    if(keynum == targetKeyNum) return true;
    return false;
}