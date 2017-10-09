@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../magento-hackathon/magento-composer-installer/bin/magento-composer-installer.php
php "%BIN_TARGET%" %*
