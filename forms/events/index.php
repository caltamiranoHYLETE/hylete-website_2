<?php
//if($_SERVER['SERVER_PORT'] != '443') { header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); exit(); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
    <title>HYLETE Event Award Redemption</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/forms/js/jquery-ui-1.11/jquery-ui.css" type="text/css" />
    <link rel="stylesheet" href="/forms/events/css/20160104_css.css?">
    <link rel="stylesheet" href="/forms/events/css/styles.css?">
    <link rel="icon" type="image/ico" href="/media/favicon.ico" />

    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="//jqueryvalidation.org/files/dist/jquery.validate.min.js"></script>
    <script src="//jqueryvalidation.org/files/dist/additional-methods.min.js"></script>
    <script type="text/javascript" src="/forms/js/challenge-global.js"></script>
    <script src="/forms/js/event-redemption-script.js"></script>
    <script src="/forms/js/ga.js?"></script>

</head>
<body>
<div id="head-container">
    <div id="logo-container"><a target="_blank" href="/"><img class="h_logo" border="0" src="/forms/img/logo-white.png" /> </a> </div>
</div>

<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modal-message">
                    <h1>Thanks!</h1>
                    <div id="loading_area">
                        <h4 id='loadingMessage'>Please wait while we create your codes.</h4><img id='loadingImage' src="/forms/img/ajax-loader.gif" border="0" />
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <div class="row clearfix">
                <div class="col-md-1 column"></div>
                <div class="col-md-10 column" id="container-first">
                    <div class="row clearfix">
                        <div class="col-md-12 column" id="container-second">

                            <h1>HYLETE Event Award Redemption <span id="event_name"></span></h1>
                                <form id="eventCodeForm" method="post" action="process.php">
                                    <p>Enter your event code in the form below to begin the process of awarding your winners. Your event code was sent in an email with the subject line "event prizes - save this email" from events@hylete.com.</p>

                                    <fieldset>
                                        <label for="event_code">Enter Your Event Code</label>
                                        <input type="text" name="event_code" id="event_code"/>
                                        <input type="submit" value="submit" style="margin-top:10px;" />
                                    </fieldset>
                                </form>
                                <div id="sectionProcessing" style="display:none">
                                    <h4>Searching for your event, please wait...</h4><img alt="loading..." src="/forms/img/ajax-loader.gif" border="0" />
                                </div>
                                <div id="errorShow" style="margin-top:20px;display:none;">
                                    <table style="width:90%">
                                        <tr>
                                            <td><h4>There was an error loading your event:</h4></td>
                                        </tr>
                                        <tr>
                                            <td id="errorMessage"></td>
                                        </tr>
                                    </table>
                                </div>

                                <div style="display:none;" id="winnerForm">

                                    <h4>Welcome! We hope your event was a success! To get your winners their award, fill out the form below completely for each person.
                                        They will receive an email with their $50 code along with redemption instructions automatically.</h4>
                                    <h4>Award Redemption Rules:</h4>
                                    <ul>
                                        <li>All emails must be unique. Sending multiple awards to a single address is not allowed.</li>
                                        <li>You cannot send awards to yourself.</li>
                                        <li>Award codes expire after 30 days of issuance.</li>
                                        <li>Any abuse of this program will result in all award codes being deactivated.</li>
                                    </ul>
                                    <form id="winner_form" method="post" action="process.php">
                                        <input type="hidden" name="event_email" id="event_email" value="" />
                                        <input type="hidden" name="event_id" id="event_id" value="" />
                                        <input type="hidden" name="event_title" id="event_title" value="" />
                                        <div class="col-md-12 column">
                                            <div class="col-md-6 column">
                                                <div class="winner_wrapper">
                                                    <h4>Winner #1</h4>
                                                    <div class="winner_block">
                                                        <fieldset>
                                                            <label for="winner_1_first_name">First Name</label>
                                                            <input id="winner_1_first_name" name="winner_1_first_name" type="text" required/>
                                                            <br/>
                                                            <label for="winner_1_last_name">Last Name</label>
                                                            <input id="winner_1_last_name" name="winner_1_last_name" type="text" required/>
                                                            <br/>
                                                            <label for="winner_1_email">Email</label>
                                                            <input id="winner_1_email" name="winner_1_email" type="text" required/>
                                                        </fieldset>
                                                    </div>
                                                </div>
                                                <div class="winner_wrapper">
                                                    <h4>Winner #3</h4>
                                                    <div class="winner_block">
                                                        <fieldset>
                                                            <label for="winner_3_first_name">First Name</label>
                                                            <input id="winner_3_first_name" name="winner_3_first_name" type="text" />
                                                            <br/>
                                                            <label for="winner_3_last_name">Last Name</label>
                                                            <input id="winner_3_last_name" name="winner_3_last_name" type="text" />
                                                            <br/>
                                                            <label for="winner_3_email">Email</label>
                                                            <input id="winner_3_email" name="winner_3_email" type="text" />
                                                        </fieldset>
                                                    </div>
                                                </div>
                                                <div class="winner_wrapper">
                                                    <h4>Winner #5</h4>
                                                    <div class="winner_block">
                                                        <fieldset>
                                                            <label for="winner_5_first_name">First Name</label>
                                                            <input id="winner_5_first_name" name="winner_5_first_name" type="text" />
                                                            <br/>
                                                            <label for="winner_5_last_name">Last Name</label>
                                                            <input id="winner_5_last_name" name="winner_5_last_name" type="text" />
                                                            <br/>
                                                            <label for="winner_5_email">Email</label>
                                                            <input id="winner_5_email" name="winner_5_email" type="text" />
                                                        </fieldset>
                                                    </div>
                                                </div>
                                                <div class="winner_wrapper">
                                                    <h4>Winner #7</h4>
                                                    <div class="winner_block">
                                                        <fieldset>
                                                            <label for="winner_7_first_name">First Name</label>
                                                            <input id="winner_7_first_name" name="winner_7_first_name" type="text" />
                                                            <br/>
                                                            <label for="winner_7_last_name">Last Name</label>
                                                            <input id="winner_7_last_name" name="winner_7_last_name" type="text" />
                                                            <br/>
                                                            <label for="winner_7_email">Email</label>
                                                            <input id="winner_7_email" name="winner_7_email" type="text" />
                                                        </fieldset>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 column">
                                                <div class="winner_wrapper">
                                                    <h4>Winner #2</h4>
                                                    <div class="winner_block">
                                                        <fieldset>
                                                            <label for="winner_2_first_name">First Name</label>
                                                            <input id="winner_2_first_name" name="winner_2_first_name" type="text" />
                                                            <br/>
                                                            <label for="winner_2_last_name">Last Name</label>
                                                            <input id="winner_2_last_name" name="winner_2_last_name" type="text" />
                                                            <br/>
                                                            <label for="winner_2_email">Email</label>
                                                            <input id="winner_2_email" name="winner_2_email" type="text" />
                                                        </fieldset>
                                                    </div>
                                                </div>
                                                <div class="winner_wrapper">
                                                    <h4>Winner #4</h4>
                                                    <div class="winner_block">
                                                        <fieldset>
                                                            <label for="winner_4_first_name">First Name</label>
                                                            <input id="winner_4_first_name" name="winner_4_first_name" type="text" />
                                                            <br/>
                                                            <label for="winner_4_last_name">Last Name</label>
                                                            <input id="winner_4_last_name" name="winner_4_last_name" type="text" />
                                                            <br/>
                                                            <label for="winner_4_email">Email</label>
                                                            <input id="winner_4_email" name="winner_4_email" type="text" />
                                                        </fieldset>
                                                    </div>
                                                </div>
                                                <div class="winner_wrapper">
                                                    <h4>Winner #6</h4>
                                                    <div class="winner_block">
                                                        <fieldset>
                                                            <label for="winner_6_first_name">First Name</label>
                                                            <input id="winner_6_first_name" name="winner_6_first_name" type="text" />
                                                            <br/>
                                                            <label for="winner_6_last_name">Last Name</label>
                                                            <input id="winner_6_last_name" name="winner_6_last_name" type="text" />
                                                            <br/>
                                                            <label for="winner_6_email">Email</label>
                                                            <input id="winner_6_email" name="winner_6_email" type="text" />
                                                        </fieldset>
                                                    </div>
                                                </div>
                                                <div class="winner_wrapper">
                                                    <h4>Winner #8</h4>
                                                    <div class="winner_block">
                                                        <fieldset>
                                                            <label for="winner_8_first_name">First Name</label>
                                                            <input id="winner_8_first_name" name="winner_8_first_name" type="text" />
                                                            <br/>
                                                            <label for="winner_8_last_name">Last Name</label>
                                                            <input id="winner_8_last_name" name="winner_8_last_name" type="text" />
                                                            <br/>
                                                            <label for="winner_8_email">Email</label>
                                                            <input id="winner_8_email" name="winner_8_email" type="text" />
                                                        </fieldset>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <fieldset>
                                            <h5>By sending these awards, <b>you will no longer be able to use this event code.</b> Make sure you have entered all the emails you need for your winners.
                                                <br/><br/>You will NOT be able to enter more after they are sent.</h5>
                                            <label style="font-size:15px;" for="acknowledge"><input type="checkbox" value="yes" name="acknowledge" id="acknowledge" required/><span style="text-transform:uppercase">I</span> acknowledge and understand&nbsp;&nbsp;</label>
                                            <br/>
                                            <input type="submit" id="send_awards" value="Send Awards" style="margin-top:10px;" >
                                        </fieldset>
                                    </form>
                                </div>
                            <br><br><br><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

</html>
