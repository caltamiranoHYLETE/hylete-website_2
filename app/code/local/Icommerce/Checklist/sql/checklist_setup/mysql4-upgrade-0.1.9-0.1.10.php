<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Checklist
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

$installer = $this;
$installer->startSetup();
$installer->run("

ALTER TABLE `{$installer->getTable('icommerce_checklist_item_checkbox_comment')}` 
ADD COLUMN `status` text NOT NULL,
ADD COLUMN `status_estimate` varchar(255) NOT NULL,
ADD COLUMN `status_updated_on` datetime NOT NULL,
ADD COLUMN `status_updated_by` varchar(255) NOT NULL,
ADD COLUMN `status_approved` tinyint(1) NOT NULL,
ADD COLUMN `status_approved_on` datetime NOT NULL,
ADD COLUMN `status_approved_by` varchar(255) NOT NULL;
    
UPDATE icommerce_checklist_item_checkbox SET text='1.1 Startsidan
* Ser startsidan korrekt ut?' WHERE text='Startsidan';

UPDATE icommerce_checklist_item_checkbox SET text='1.2 Menyn
* Visas alla nivåer i menyn?' WHERE text='Menyn';

UPDATE icommerce_checklist_item_checkbox SET text='1.3 Filternavigering
* Om du har en filternavigering, fungerar den som vanligt?' WHERE text='Lagerbaserad navigation';
  
UPDATE icommerce_checklist_item_checkbox SET text='1.4 Listläge
* Visas produktlistningen som den ska i kategorierna?' WHERE text='Listläge'; 

UPDATE icommerce_checklist_item_checkbox SET text='1.5 Övriga anpassningar
* Fungerar eventuella tillägg som de ska i kategorivyn?' WHERE text='Övriga anpassningar' AND item_id='1';

UPDATE icommerce_checklist_item_checkbox SET text='2.1 Bilder och miniatyrer
* Visas alla produktbilder?' WHERE text='Bilder och miniatyrer';

UPDATE icommerce_checklist_item_checkbox SET text='2.2 Prisvisning
* Visas priserna med korrekt moms?
* Syns priserna som de ska?' WHERE text='Prisvisning';

UPDATE icommerce_checklist_item_checkbox SET text='2.3 Prisskillnader i konfigurerbara produkter
* Finns det eventuella prisskillnader?' WHERE text='Pris skillnader i konfigurerbara produkter';

UPDATE icommerce_checklist_item_checkbox SET text='2.4 Anpassade val
* Finns det eventuella anpassade val?' WHERE text='Anpassade val';

UPDATE icommerce_checklist_item_checkbox SET text='2.5 Finns produkterna kvar
* Försvinner produkterna från framsidan medan de finns kvar i admin? Om nej, markera detta som OK.' WHERE text='Se till att produkterna finns kvar 24h efter testuppgradering';

UPDATE icommerce_checklist_item_checkbox SET text='2.6 Övriga anpassningar
* Fungerar eventuella tillägg som de ska i produktvyn?' WHERE text='Övriga anpassningar' AND item_id='2';  

UPDATE icommerce_checklist_item_checkbox SET text='3.1 Skapa ordrar
* Skapa ordrar i kombination av alla betalnings- och fraktmetoder.
* Upprepa detta på alla multisiter.' WHERE text='Lägg ordrar i kombination av alla betalnings- metoder och multisiter';

UPDATE icommerce_checklist_item_checkbox SET text='3.2 Inloggad kund och gäst
* Skapa ordrar som inloggad kund och gäst.' WHERE text='Lägg ordrar som inloggad kund och gäst';

UPDATE icommerce_checklist_item_checkbox SET text='3.3 Slutför ordrar
* Slutför minst 1 order per betalningsmetod.' WHERE text='Slutför ordrar';

UPDATE icommerce_checklist_item_checkbox SET text='3.4 Kreditera ordrar
* Kreditera minst 1 order per betalningsmetod.' WHERE text='Kreditera ordrar';

UPDATE icommerce_checklist_item_checkbox SET text='3.5 Makulera ordrar
* Makulera minst 1 order per betalningsmetod.' WHERE text='Makulera ordrar';

UPDATE icommerce_checklist_item_checkbox SET text='3.6 Skriv ut PDF:er
* Skriv ut ordrar, fraktsedlar, fakturor och kreditnotor.' WHERE text='Skriv ut ordrar, fraktsedlar, fakturor och kreditnotor';

UPDATE icommerce_checklist_item_checkbox SET text='3.7 Övriga anpassningar
* Fungerar eventuella tillägg i kassan eller försäljningsadministrationen?' WHERE text='Övriga anpassningar' AND item_id='3'; 

UPDATE icommerce_checklist_item_checkbox SET text='4.1 Logga in som kund
* Går det att logga in på framsidan?
* Fungerar alla menyer under Mitt konto?' WHERE text='Logga in som kund';

UPDATE icommerce_checklist_item_checkbox SET text='4.2 Lagda ordrar under Mitt konto
* Visas ordrarna korrekt under Mitt konto?' WHERE text='Kontrollera lagda ordrar under mitt konto';

UPDATE icommerce_checklist_item_checkbox SET text='4.3 Kundgrupper och prisregler
* Fungerar det att logga in med olika kundgrupper?
* Får man korrekt rabatt beroende på vilken kundgrupp man tillhör, om sådana prisregler existerar.' WHERE text='Testa olika kundgrupper som t.ex. prisregler';

UPDATE icommerce_checklist_item_checkbox SET text='4.4 Ändra kund i admin
* Går det att ändra kundinformation i admin?' WHERE text='Editera kund i admin';

UPDATE icommerce_checklist_item_checkbox SET text='4.5 Övriga anpassningar
* Fungerar eventuella tillägg gällande kundhanteringen?' WHERE text='Övriga anpassningar' AND item_id='4';

UPDATE icommerce_checklist_item_checkbox SET text='5.1 Kontrollera aktiva prisregler
* Tillämpas det korrekt moms och rabatt på aktiva prisregler?
* Följer rabatten med till 3:e parts system som t.ex. Klarna/DIBS/Auriga/E-conomic?' WHERE text='Kontrollera aktiva prisregler';

UPDATE icommerce_checklist_item_checkbox SET text='5.2 Nyhetsbrev
* Om Magentos nyhetsbrevsfunktion används, fungerar denna?
* Om externa nyhetsbrevsfunktioner som t.ex. MailChimp används, fungerar dessa?' WHERE text='Nyhetsbrev och kopplingar som t.ex. MailChimp';

UPDATE icommerce_checklist_item_checkbox SET text='6.1 Hantera sidor
* Går det att lägga till/ta bort CMS-sidor?
* Går det att redigera text/lägga till bilder?' WHERE text='Hantera sidor';

UPDATE icommerce_checklist_item_checkbox SET text='6.2 Hantera CMS block
* Går det att lägga till/ta bort CMS-block?
* Går det att redigera text/lägga till bilder?' WHERE text='Hantera CMS block';

UPDATE icommerce_checklist_item_checkbox SET text='6.3 Hantera blogg
* Går det att lägga till/ta bort blogginlägg?
* Går det att redigera text/lägga till bilder?' WHERE text='Hantera blogg och dess bilder';

UPDATE icommerce_checklist_item_checkbox SET text='6.4 Övriga anpassningar
* Fungerar eventuella tillägg gällande CMS?' WHERE text='Övriga anpassningar' AND item_id='6';

UPDATE icommerce_checklist_item_checkbox SET text='7.1 Använda rapporter
* Kontrollera dina mest använda rapporter.
* Stämmer redovisningarna?' WHERE text='Kontrollera dina mest använda rapporter';

UPDATE icommerce_checklist_item_checkbox SET text='7.2 Övriga anpassningar
* Fungerar eventuella tillägg gällande specialanpassade rapporter?' WHERE text='Övriga anpassningar' AND item_id='7';

UPDATE icommerce_checklist_item_checkbox SET text='8.1 E-postmallar
* Innehåller några e-postmallar felaktig information?
* Fungerar alla e-postutskick från Magento? Exempel:
* - Glömt lösenord
* - Nytt konto
* - Orderbekräftelse (ny order)
* - Leveransbekräftelse (ny leverans)
* - Återköpsbekräftelse (ny kreditnota/kreditfaktura)' WHERE text='E-postmallar';

UPDATE icommerce_checklist_item_checkbox SET text='8.2 Fraktalternativ
* Fungerar alla fraktalternativ som finns aktiverade i admin?' WHERE text='Fraktalternativ';

UPDATE icommerce_checklist_item_checkbox SET text='8.3 Betalningsmetoder
* Fungerar alla betalningsmetoder som finns aktiverade i admin?' WHERE text='Betalningsmetoder';

UPDATE icommerce_checklist_item_checkbox SET text='8.4 API-integrationer
* Fungerar eventuella integrationer som t.ex. E-conomic, Visma, Specter, MailChimp?' WHERE text='API-integrationer som t.ex. E-conomic och Visma';

UPDATE icommerce_checklist_item_checkbox SET text='8.5 Övriga anpassningar
* Fungerar eventuella tillägg i admin?' WHERE text='Övriga anpassningar' AND item_id='8';

UPDATE icommerce_checklist_item SET name='1. Kategorivy' WHERE name='Kategorivy';
UPDATE icommerce_checklist_item SET name='2. Produktvy' WHERE name='Produktvy';
UPDATE icommerce_checklist_item SET name='3. Försäljning & beställningar' WHERE name='Försäljning & beställningar';
UPDATE icommerce_checklist_item SET name='4. Kunder' WHERE name='Kunder';
UPDATE icommerce_checklist_item SET name='5. Marknadsföring & nyhetsbrev' WHERE name='Marknadsföring & nyhetsbrev';
UPDATE icommerce_checklist_item SET name='6. CMS' WHERE name='CMS';
UPDATE icommerce_checklist_item SET name='7. Rapporter' WHERE name='Rapporter';
UPDATE icommerce_checklist_item SET name='8. System' WHERE name='System';

UPDATE icommerce_checklist_item_checkbox SET text='1.1 Produkter
* Är alla produkter inlagda? 
* Är alla produkter aktiverade? 
* Tillhör alla produkter minst en produktkategori?
* Är priser på produkter korrekt angivna?' WHERE text='1.1 Produkter';

UPDATE icommerce_checklist_item_checkbox SET text='1.2 Moms
* Är priserna konsekvent i exkl. alternativt inkl. moms?' WHERE text='1.2 Moms';

UPDATE icommerce_checklist_item_checkbox SET text='2.1 Startsida
* Innehåller din startsida en huvudrubrik (h1:a)?
* Innehåller din h1:a tre av dina viktigaste sökord?' WHERE text='2.1 Startsida';

UPDATE icommerce_checklist_item_checkbox SET text='2.2 CMS-sidor
* Visar alla länkar rätt information?
* Kontrollera texterna i dina sidor, som t.ex: Köpvillkor & Om oss' WHERE text='2.2 CMS-sidor';

UPDATE icommerce_checklist_item_checkbox SET text='2.3 Länkar på sidan
* Säkerställ alla övriga länkar i din butik.' WHERE text='2.3 Länkar på sidan';

UPDATE icommerce_checklist_item_checkbox SET text='2.4 CMS-block
* Innehåller alla block det innehåll som du vill visa?' WHERE text='2.4 CMS-block';

UPDATE icommerce_checklist_item_checkbox SET text='3.1 E-postadresser för butik
* Har du ställt in dina avsändare med e-postadresser som kund ska se när de får automatiska e-postmeddelande från butiken?' WHERE text='3.1 E-postadresser för butik';

UPDATE icommerce_checklist_item_checkbox SET text='3.2 E-postmallar
* Gör inga ändringar på logotyp!
* Använd UploadLogo för att ändra logotyp.
* Har du testat att ta emot e-postmallarna i din inkorg?
* Visar alla e-postmallar rätt innehåll?' WHERE text='3.2 E-postmallar. OBS gör inga ändringar på logotyp.';

UPDATE icommerce_checklist_item_checkbox SET text='3.3 E-postadresser för olika formulär
* Har du kontrollerat följande:
* System >> Konfiguration >> Försäljning e-post.
* System >> Konfiguration >> E-posta till en vän.
* System >> Konfiguration >> Nyhetsbrev.
* System >> Konfiguration >> Kundinställningar.
* System >> Konfiguration >> Önskelista.
* System >> Konfiguration >> Kontakter >> Alternativ för e-post.' WHERE text='3.3 E-postadresser för olika formulär';

UPDATE icommerce_checklist_item_checkbox SET text='4.1 Betalningssätt & frakt
* Visas alla frakt- och betalningssätt i kassan?' WHERE text='4.1 Betalningssätt';

UPDATE icommerce_checklist_item_checkbox SET text='5.1 Tillåta gäster
* Ska du tillåta gäster att använda kassan?
* Om du har vår snabbkassa, kommer alla som handlar att få ett kundkonto.' WHERE text='5.1 Ska du tillåta gäster att använda kassan?';

UPDATE icommerce_checklist_item_checkbox SET text='5.2 Presentmeddelanden
* Ska man kunna skriva ett presentmeddelande i kassan?' WHERE text='5.2 Presentmeddelanden';

UPDATE icommerce_checklist_item_checkbox SET text='5.3 Kvalitetssäkra
* Har du genomfört köp med alla fraktsätt?
* Har du genomfört köp med alla betalningssätt?
* Har du ställt alla betalningssätt i live läge?
* Har du kontrollerat alla beställningar i Magento admin?
* Har du upprepat detta för alla enventuella multisiter?
* Har du kontrollerat att rätt information skickas till t.ex. Klarna/DIBS/Auriga/E-conomic?' WHERE text='5.3 Kvalitetssäkra';

UPDATE icommerce_checklist_item_checkbox SET text='6.1 Varukorgen
* Visas det korrekta summor och rabatter?
* Är momsen korrekt beräknat för tillåtna leveransländer och olika momsklasser?' WHERE text='6.1 Varukorgen';

UPDATE icommerce_checklist_item_checkbox SET text='6.2 Övriga översättningar
* Har du översatt engelska översättningar eller konstiga termer?' WHERE text='6.2 Övriga översättningar';

UPDATE icommerce_checklist_item_checkbox SET text='7.1 Peka om
* Är du redo att gå live?
* Kontakta oss innan du pekar om din domän.' WHERE text='7.1 Peka om';
    
");

$installer->endSetup();
