<!-- 

CS50's Binary Bulbs

by Michael Ge, 2015

-->

<?php require(__DIR__ . "/../../lib/functions.php"); ?>

<!DOCTYPE html>
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <meta name="title" content="CS50 Binary Bulbs"/>
        <meta name="description" content="Learn to count in binary with CS50's Binary Bulbs!"/>

        <?php /* TODO: PNG missing
        <link rel="image_src" href="<?= img_encode("img/bulbs-thumb.png") ?>"/>
        */ ?> 

        <?php

            $styles = [
                "css/bootstrap.min.css",
                "css/bootstrap-toggle.css",
                "css/jquery.qtip.css",
                "css/bootstrap-dialog.css",
                "css/styles.css"
            ];
            foreach ($styles as $style) {
                print("\n<style>\n");
                print(file_get_contents($style));
                print("\n</style>\n");
            }

        ?>

        <style>

            /* TODO: move custom CSS from css/styles.css here, then remove css/styles.css mention above */

        </style>

        <?php

            $scripts = [
                "js/jquery.js",
                "js/jquery.qtip.js",
                "js/bootstrap.min.js",
                "js/bootstrap-toggle.js",
                "js/bootstrap-dialog.js"
            ];
            foreach ($scripts as $script) {
                print("\n<script>\n");
                print(file_get_contents($script));
                print("\n</script>\n");
            }

        ?>

        <script>

            // Max value of bulb representation
            var MAX = 256;
            // Number of bulbs
            var IDS = 8;
            // SFX toggler
            var FLAG = true;
            // Easter Egg
            var easter_egg = true;
            //max width of screen
            var WIDTH = Math.max(screen.width, screen.height);
            //ON and OFF bulb directories
            var ON = "<?= img_encode("img/bin_on_med.png") ?>";
            var OFF = "<?= img_encode("img/bin_off_med.png") ?>";

            $(function () {
                //Checks for IE <= 9 and shows apology page.
                if (msieversion() < 0 || msieversion() >= 10) {
                    
                    // Initial / resized scaling.
                    scale();
                    $(window).resize(function(){
                        scale();
                    });
                    
                    // Instantiates tooltips.
                    tooltips(".bulb", "toggle state");
                    tooltips(".labels", "toggle state");
                    tooltips(".on", "toggle state");
                    tooltips("#up", "increment value");
                    tooltips("#down", "decrement value");
                    tooltips("#decimal", "value in decimal");
                    
                    // Creates bulb triggers.
                    for (var k = 0; k < IDS; k++) {
                        addButton("#bulb" + k);
                    }
                    
                    // Creates label triggers for bulb click.
                    $(".labels, .on").each(function() {
                        $(this).click(function() {
                            $(this).siblings(".bulb").trigger("click");
                        });
                    });
                    
                    // Creates controller arrows.
                    addButton("#up");
                    addButton("#down");
                
                    // Creates game button.
                    addToggles("gameMode", false, "#gameSwitch");
                    $("#gameLabel").on("click", function() {
                        var x = $("#gameSwitch").prop("checked") === true ? "off" : "on";
                        $("#gameSwitch").bootstrapToggle(x);
                    });
                    $("#gameSwitch").change(function() {
                        $("#gameVal").html("");
                        $("#game").css("visibility", "hidden");
                        changeMode();
                    });
                    
                    // Creates label button.
                    addToggles("bitMode", true, "#bitSwitch");
                    $("#bitLabel").on("click", function() {
                        var x = $("#bitSwitch").prop("checked") === true ? "off" : "on";
                        $("#bitSwitch").bootstrapToggle(x);
                    });
                    $("#bitSwitch").change(function() {
                        toggleLabels();
                    });
                
                    // Creates bulb button.
                    addToggles("bulbsMode", true, "#bulbSwitch");
                    $("#bulbsMode").on("click", function() {
                        var x = $("#bulbSwitch").prop("checked") === true ? "off" : "on";
                        $("#bulbSwitch").bootstrapToggle(x);
                    });
                    $("#bulbSwitch").change(function() {
                        toggleBulbs();
                    });
                    
                    // Loads previous cache.
                    restoreCache();
                }
                else {
                    $("body").html("Sorry! This widget requires a newer browser.");
                }
            });

            // Handles IE <= 9
            function msieversion() {
                    var ua = window.navigator.userAgent;
                    var msie = ua.indexOf("MSIE ");
                    // If Internet Explorer, return version number
                    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))
                        return parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)), 10);
                    else
                        return -1;
            }

            // Handles window scaling for buttons and bulbs.
            function scale() {
                var newWidth = Math.max(0.7, $(window).width() / WIDTH * 1.4);
                $(".button").each(function() {
                    $(this).css("transform", "scale(" + newWidth + ", " + newWidth + ")");  
                });
                $(".bulb").each(function() {
                    $(this).css("max-width", $(window).width() / (IDS + 2));
                });
                $(".on").each(function() {
                    $(this).css("max-width", $(window).width() / (IDS + 2));
                });
                $(".box").each(function() {
                    $(this).css("width", $(window).width() / (IDS + 2));
                });
            }

            function tooltips(c, str) {
                $(c).qtip({
                   content: { text: str },
                   position: {
                      target: 'mouse',
                      adjust: {
                        x: 15,
                        y: 20,
                        mouse: true,
                        resize: true
                      }
                   },
                   show: { delay: 2000 },
                   style: { classes: 'qtip-tipsy' }
                });
            }

            // Creates button, listener, and game success.
            function addButton(idName) {
                
                // Disables image dragging.
                $(idName).on("dragstart", function() { return false; });
                
                // Listens for click and computes decimal value.
                var sum;
                $(idName).click(function() {
                    if ($(idName).siblings(".on").eq(0).css("top") == "0px") {
                        sum = changeBy(-(parseInt($(idName).attr("value"), 10)));
                    }
                    else {
                        sum = changeBy(parseInt($(idName).attr("value"), 10));
                    }   
                    // Easter Egg
                    if (easter_egg && $("#decimal").html() === "42") {
                        var alrt = new BootstrapDialog({
                                    title: "That's the Answer to the Great Question Life, "+ 
                                    "the Universe, and Everything!",
                                    message: function(dialogRef) {
                                        var $btn = $(' <button type="submit" class="btn ' + 
                                                'btn-primary btn-block">OK</button></div>');
                                        $btn.on('click', {z: dialogRef}, function(event) {
                                            event.data.z.close();
                                        });
                                        return $btn;
                                    }
                                });
                                alrt.realize();
                                alrt.getModalFooter().hide();
                                alrt.setClosable(false);
                                alrt.getModalBody().css('color', '#000');
                                alrt.open();
                    }
                    // Checks for game mode and matching value. Then celebrates and resets.
                    if ($("#gameSwitch").prop("checked")
                        && $("#gameVal").html() == sum) 
                    {
                        $("#game").html("You win!");
                        var celebrate = setInterval(function() {
                            localStorage.setItem("gameMode", "false");
                            var a = FLAG ? 0 : 1;
                            for (var k = 0; k < IDS; k++) {
                            if (k%2 == a) {
                                    $("#bulb" + k + "on").css("top", 0);
                                    if ($("#bulbSwitch").prop("checked") === false) {
                                        $("#bulb" + k).css("opacity", 0);
                                    }
                                }
                                else {
                                    $("#bulb" + k + "on").css("top", -100000);
                                    if ($("#bulbSwitch").prop("checked") === false) {
                                        $("#bulb" + k).css("opacity", 100);
                                    }
                                }
                            }
                            FLAG = !FLAG;
                        }, 100);
                        // Party over.
                        setTimeout(function() {
                            clearInterval(celebrate);
                            var val = parseInt($("#decimal").html());
                            resetBulbs();
                            changeBy(val);
                            $("#game").html("Represent the number <span id=\"gameVal\">" + 
                                            "</span> in binary");        
                            $("#gameSwitch").bootstrapToggle("off");
                            cache();
                        }, 3000);
                    }
                });
            }   

            /* Adds n to decimal value, updating bulbs image. 
            ** Returns new sum and dec tooltip */
            function changeBy(n) {
                var sum = parseInt($("#decimal").html(), 10) + n;
                var sum2 = sum;
                if (sum >= MAX || sum < 0) {
                    return false;
                }
                else {
                    resetBulbs();
                    $("#decimal").html(sum);
                    for (var k = 0; k < IDS; k++) {            
                        if ($("#bulb" + k).attr("value") <= sum) {
                            if ($("#bulbSwitch").prop("checked") === false) {
                                $("#bulb" + k).css("opacity", 0);
                            }
                            $("#bulb" + k + "on").css("top", 0);
                            sum -= parseInt($("#bulb" + k).attr("value"), 10);
                        }
                    }
                }
                cache();
                return sum2;
            }

            //toggles between game and play mode
            function changeMode() {
                if ($("#gameSwitch").prop("checked")) {
                    setGameValue();
                }
                else {
                    $("#gameVal").html("");
                    $("#game").css("visibility", "hidden");
                    cache();
                }
            }

            //Prompts user for number, defaulting to random. Lots of ugly alert box hacks.
            function setGameValue() {
                var dialog = new BootstrapDialog({
                    title: "Input a number between 1 and 255.",
                    message: function(dialogRef){
                        var $message = $('<div><input autofocus type="text"' + 
                                            'class="form-control" id="gval">');
                        var $button = $('<button type="submit" id="gok" class="btn ' + 
                                            'btn-primary btn-block">OK</button></div>');
                        $button.on('click', {dialogRef: dialogRef}, function(event) {
                            var result = $("#gval").val();
                            if (result === null) {
                                $("#gameSwitch").bootstrapToggle("off");
                            }
                            else if (isNaN(result)) {
                                makeAlert("Input must be a number!");
                                return false;
                            }
                            else if (result <= 0 || result > 255) {
                                makeAlert("Number must be between 1 and 255!");
                                return false;
                            }
                            else if (result%1 != 0) {
                                makeAlert("Number must be an integer!");
                                return false;
                            }
                            else {
                                $("#gameVal").html(Math.floor(result));
                                $("#game").css("visibility", "visible");
                                resetBulbs();
                                cache();
                            }
                            event.data.dialogRef.close();
                        });
                        $message.append($button);
                        return $message;
                    },
                    closable: false,
                    onshown: function() { 
                        $("#gval").focus(); 
                    },
                    onhide: function() { 
                        if ($("#gval").val() === '') 
                            $("#gameSwitch").bootstrapToggle("off"); 
                    }
                });
                dialog.realize();
                dialog.getModalFooter().hide();
                dialog.setClosable(true);
                dialog.getModalBody().css('color', '#fff');
                dialog.open();
            }

            // Toggles binary bulb labels.
            function toggleLabels() {
                $("figcaption").each(function() {
                    if ($(this).css("visibility") == "hidden") {
                        $(this).css("visibility", "visible");
                    }
                    else {
                        $(this).css("visibility", "hidden");
                    }
                });
                cache();
            }

            // Toggles between bulb and binary modes.
            function toggleBulbs() {
                $(".on").each(function() {
                    if ($(this).attr("src") === ON) {
                        $(this).attr("src", "<?= img_encode("img/bin_on_med_1.png") ?>");
                    }
                    else {
                        $(this).attr("src", ON);
                    }
                });
                $(".bulb").each(function() {
                    if ($(this).attr("src") === OFF) {
                        $(this).attr("src", "<?= img_encode("img/bin_off_med_1.png") ?>");
                    }
                    else {
                        $(this).attr("src", OFF);
                    }
                });
                changeBy(0);
                cache();
            }

            // Sets all bulbs to off mode.
            function resetBulbs() {
                $(".on").each(function() {
                    $(this).css("top", -100000);
                });
                $(".bulb").each(function() {
                    $(this).css("opacity", 100);
                });
                $("#decimal").html(0);
            }

            // Adds toggle switches.
            function addToggles(item, def, id) {
                if (localStorage.getItem(item) === null) {
                    $(id).bootstrapToggle(def ? "on" : "off");
                }
                else {
                    $(id).bootstrapToggle(
                        (localStorage.getItem(item) == "true") ? "on" : "off");
                }
            }

            // Caches important data
            function cache() {
                localStorage.setItem("bulbs", $("#decimal").html());
                localStorage.setItem("gameVal", $("#gameVal").html());
                localStorage.setItem("gameMode", $("#gameSwitch").prop('checked'));
                localStorage.setItem("bitMode", $("#bitSwitch").prop('checked'));
                localStorage.setItem("bulbsMode", $("#bulbSwitch").prop('checked'));
            }

            // Loads cache
            function restoreCache() {
                //localStorage.clear();
                
                //set game value
                if (localStorage.getItem("gameMode") == "true") {
                    $("#game").css("visibility", "visible");
                    var gameVal = localStorage.getItem("gameVal");
                    if (gameVal !== null) {
                        $("#gameVal").html(parseInt(gameVal, 10));
                    }
                }
                
                // set bulbs
                var bulbs = localStorage.getItem("bulbs");
                if (bulbs !== null) {
                    changeBy(parseInt(bulbs, 10));
                    $("#decimal").html(bulbs);
                }    
                
                // set bit labels
                if (localStorage.getItem("bitMode") == "false") {
                    $("figcaption").each(function() {
                        $(this).css("visibility", "hidden");
                    });
                }
                
                // set whether bulbs or binary digits
                if (localStorage.getItem("bulbsMode") == "false") {
                    toggleBulbs();
                }
            }

            // Makes alert popup boxes.
            function makeAlert(str) {
                var alrt = new BootstrapDialog({
                    title: str,
                    message: function(dialogRef) {
                        var $btn = $('<button type="submit" class="btn btn-primary btn-block">OK</button></div>');
                        $btn.on('click', {dialogRef: dialogRef}, function(event) {
                            event.data.dialogRef.close();
                        });
                        return $btn;
                    }
                });
                alrt.realize();
                alrt.getModalFooter().hide();
                alrt.setClosable(false);
                alrt.getModalBody().css('color', '#000');
                alrt.open();
            }

        </script>

        <title>Binary Bulbs</title>

    </head>
    
    <body>
    <center> <!-- Fixes off-center down button...-->
    <div class="container">
        <div class="images">
            <?php
                $ids = 8;
                $MAX = 256;
                for ($k = 0; $k < $ids; $k++)
                {
                    $MAX /= 2;
                    print("<figure class=\"box\">
                    <figcaption value=\"" . $MAX . "\" class=\"text labels\" id=\"label" . 
                    $k . "\">" . $MAX . "</figcaption>
                    <img value=\"" . $MAX . "\" class=\"bulb\" id=\"bulb" . 
                    $k . "\" src=\"" . img_encode("img/bin_off_med.png") . "\" data-toggle=\"tooltip\" 
                    data-placement=\"bottom\" title=\"toggle state\">
                    <img class=\"on\" id=\"bulb" . $k . "on\" 
                    src=\"" . img_encode("img/bin_on_med.png") . "\" data-toggle=\"tooltip\" 
                    data-placement=\"bottom\" title=\"toggle state\"></figure>");
                }
            ?>
        </div>


        <div id="controller">
            <img value="1" class="ctrlButtons" id="up" src="<?= img_encode("img/up.png") ?>" 
                data-toggle="tooltip" data-placement="right" title="increment value"><br> 
            <span data-toggle="tooltip" title="value in decimal" 
                data-placement="right" class="text" id="decimal">0</span><br>
                <img value="-1" id="down" class="ctrlButtons" src="<?= img_encode("img/down.png") ?>" 
                data-toggle="tooltip" data-placement="right" title="decrement value">
            <p class="text" id="game">
                How do you represent the number
                    <span id="gameVal"></span>
                in binary?
            </p>
        </div>
         
        <ul id="settings">
            <li>
            <div class="button" id="button3">
                <p class="text" id="bulbsMode">Bulbs</p>
                <input id="bulbSwitch" class="toggle-group"
                checked type="checkbox" data-toggle="toggle" data-size="mini">    
            </div>
            </li>
            <li>
            <div class="button" id="button1">
                <p class="text" id="gameLabel">Game Mode</p>
                <input id="gameSwitch" class="toggle-group" 
                    unchecked type="checkbox" data-toggle="toggle" data-size="mini">
            </div>
            </li>
            <li>
            <div class="button" id="button2">
            <p class="text" id="bitLabel">Labels</p>
                <input id="bitSwitch" class="toggle-group" 
                    checked type="checkbox" data-toggle="toggle" data-size="mini">    
            </div>
            </li>
        </ul>
        <br>
        <p class="text" id="footer">Binary Bulbs by 
            <a href="https://cs50.harvard.edu/">CS50</a></p>
    </div>
    </center>
    </body>
</html>