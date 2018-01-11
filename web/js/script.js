"use strict";

(function () {
    // ======== private vars ========
    var session, timerId;

    function init() {
        $('#connect').on('click', connectButtonHandler);
    }

    function connectButtonHandler() {
        if (session) {
            session.close();

            $('#connect').val('Connect');
        } else {
            ab.connect(
                $('#server').val(),
                onOpenCallback,
                onCloseCallback
            );
        }
    }

    //open connection callback
    function onOpenCallback() {
        session = arguments[0];

        $('#connect').val('Disconnect');

        $('#run').on('click', runElevator);
        $('#stop').on('click', stopElevator);

        log('system', 'Connection established');
    }

    //close connection callback
    function onCloseCallback() {
        session = undefined;

        $('#connect').val('Connect');

        log('system', 'Connection closed:(', arguments);
    }

    function runElevator() {
        createController();

        //subscribe on server events
        subscribeEvents(true);

        //bind UI components handlers
        bindHandlers(true);

        //run passengers
        timerId = setInterval(generateWeight, parseInt($('#weightInterval').val()) * 1000);

        //create elevator's controller
        publish('app.controller.create', {
            floors: $('#floors').val(),
            router: $('#router').val(),
            weightMax: $('#weightMax').val(),
            weightMin: $('#weightMin').val(),
            weightNonStop: $('#weightNonStop').val()
        });

        log('system', 'Elevator run');

        function createController() {
            var floors = $('#floors').val(),
                elevator =
                '<td rowspan="' + floors + '"><div class="elevator" style="background-position: 0px 500px">';

            for (var i=floors-1; i>=0; i--) {
                elevator += '<div class="door ' + ((i === 0) ? 'open' : 'close') +  '" floor="' + i + '"></div>';
            }

            elevator +=
                '</div>' +
            '</td>';

            var result =
                '<table>' +
                    '<tr>' +
                        '<td>';

                            //кнопки этажей
                            result += '<table class="house">';

                            for (i=floors-1; i>=0; i--) {
                                result +=
                                    '<tr>' +
                                        elevator +
                                        '<td class="floorNumber">' + (i + 1) + '</td>' +
                                        '<td>Call elevator: <input type="checkbox" name="controllerFloorButton" value="' + i + '"></td>' +
                                        '<td>Weight: <input type="text" name="weight" floor="' + i + '" value="" size="2"></td>' +
                                    '</tr>';

                                elevator = '';
                            }

                            result += '</table>';

            result += '</td><td>';

                //панель управления лифтом
                result +=
                    '<table class="panel">' +
                    '<tr>' +
                        '<td class="floorScreen">1</td>' +
                    '</tr>';

                    //кнопки этажей
                    for(i=floors-1; i>=0; i--) {
                        result +=
                            '<tr>' +
                                '<td>' +
                                    '<input type="checkbox" name="elevatorFloorButton" value="' + i + '" /> ' + (i + 1) +
                                '</td>' +
                            '</tr>';
                    }

                    //кнопка открытия/закрытия дверей
                    result +=
                        '<tr>' +
                            '<td>' +
                                '<input type="button" name="elevatorOpenButton" value="Open" /> ' +
                                '<input type="button" name="elevatorCloseButton" value="Close" />' +
                            '</td>' +
                        '</tr>';

                    result +=
                        '<tr>' +
                            '<td class="weightSensorScreen">0</td>' +
                        '</tr>';

                result += '</table>';

            result +=
                    '</td>' +
                '</tr>' +
            '</table>';

            $('#controller').html(result);
        }
    }

    function stopElevator() {
        //stop passengers
        if (timerId) {
            clearInterval(timerId);
        }

        $('#controller').html('');

        log('system', 'Elevator stopped');
    }

    function subscribeEvents(subscribe) {
        var method = subscribe ? 'subscribe' : 'unsubscribe';

        session[method]('app.controller.created', controllerCreated);
        session[method]('app.controller.floor.turned_off', controllerFloorTurnedOff);
        session[method]('app.controller.floor.turned_on', controllerFloorTurnedOn);
        session[method]('app.elevator.door.closed', elevatorDoorClosed);
        session[method]('app.elevator.door.opened', elevatorDoorOpened);
        session[method]('app.elevator.floor_changed', elevatorFloorChanged);
        session[method]('app.elevator.floor.turned_on', elevatorFloorTurnedOn);
        session[method]('app.elevator.floor.turned_off', elevatorFloorTurnedOff);
        session[method]('app.elevator.moved', elevatorMoved);
        session[method]('app.elevator.weight_sensor.changed', elevatorWeightSensorChanged);
        session[method]('app.elevator.weight_sensor.less', elevatorWeightSensorLess);
        session[method]('app.elevator.weight_sensor.over', elevatorWeightSensorOver);

        function controllerCreated(topic, data) {
            log('in', topic, data);
        }

        function controllerFloorTurnedOff(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            $('[name=controllerFloorButton][value=' + data.floor + ']').prop('checked', false);
        }

        function controllerFloorTurnedOn(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            $('[name=controllerFloorButton][value=' + data.floor + ']').prop('checked', true);
        }

        function elevatorDoorClosed(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            $('.door[floor=' + data.floor + ']').removeClass('open').addClass('close');
        }

        function elevatorDoorOpened(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            //открываем двери
            $('.door[floor=' + data.floor + ']').removeClass('close').addClass('open');

            passengersOut(data.floor);

            passengersIn(data.floor);

            function passengersOut(floor) {
                var weight = parseInt($('.weightSensorScreen').html()) || 0,
                    elevatorFloors = $('[name=elevatorFloorButton]:checked').toArray().map(function(button) {
                        return parseInt(button.value);
                    });

                if (weight === 0) {
                    return;
                }

                if (elevatorFloors.length === 0 || elevatorFloors.indexOf(floor) !== -1) {

                    weight = elevatorFloors.length > 1 ? parseInt(weight / elevatorFloors) : 0;

                    publish('app.elevator.out', {
                        weight: weight
                    });

                    setTimeout(function() {
                        log('process', 'Passengers left elevator')
                    }, 1000);
                }
            }

            function passengersIn(floor) {
                var weight = $('[name=weight][floor=' + floor + ']');

                publish('app.elevator.in', {
                    weight: parseInt(weight.val())
                });

                weight.val('');
            }
        }

        function elevatorFloorChanged(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            $('.floorScreen').html(parseInt(data.floor) + 1);
        }

        function elevatorFloorTurnedOn(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            $('[name=elevatorFloorButton][value=' + data.floor + ']').prop('checked', true);
        }

        function elevatorFloorTurnedOff(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            $('[name=elevatorFloorButton][value=' + data.floor + ']').prop('checked', false);
        }

        function elevatorMoved(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            var elevator = $('.elevator'),
                position = parseInt(elevator.css('background-position').split(' ')[1]) + parseInt(data.offset);

            elevator.css('background-position', '0px ' + position + 'px');
        }

        function elevatorWeightSensorChanged(topic, data) {
            log('in', topic, data);

            data = JSON.parse(data);

            $('.weightSensorScreen')
                .removeClass('weight_sensor_screen__less')
                .removeClass('weight_sensor_screen__over')
                .html(data.value);
        }

        function elevatorWeightSensorLess(topic, data) {
            elevatorWeightSensorChanged(topic, data);

            $('.weightSensorScreen').addClass('weight_sensor_screen__less');
        }

        function elevatorWeightSensorOver(topic, data) {
            elevatorWeightSensorChanged(topic, data);

            $('.weightSensorScreen').addClass('weight_sensor_screen__over');
        }
    }

    //bind/unbind event handlers to UI components
    function bindHandlers(bind) {
        var method = bind ? 'on' : 'off';

        //controller
        $('[name=controllerFloorButton]')[method]('click', floorButtonClickHandler);

        //elevator
        $('[name=elevatorFloorButton]')[method]('click', elevatorFloorButtonClickHandler);
        $('[name=elevatorOpenButton]')[method]('click', elevatorOpenButtonClickHandler);
        $('[name=elevatorCloseButton]')[method]('click', elevatorCloseButtonClickHandler);

        //controller floor button handler
        function floorButtonClickHandler() {
            publish('app.controller.floor.press', {
                floor: this.value
            });
        }

        //elevator floor button handler
        function elevatorFloorButtonClickHandler() {
            publish('app.elevator.floor.press', {
                floor: this.value
            });
        }

        //elevator open button handler
        function elevatorOpenButtonClickHandler() {
            publish('app.elevator.open.press');
        }

        //elevator close button handler
        function elevatorCloseButtonClickHandler() {
            publish('app.elevator.close.press');
        }
    }

    //generate weight
    function generateWeight() {
        var floor = random(0, $('#floors').val()),
            input = $('[name=weight][floor=' + floor + ']'),
            weight = parseInt(input.val()) + 80 || 80,
            controllerFloorButton = $('[name=controllerFloorButton][value=' + floor + ']'),
            open = $('.open[floor=' + floor + ']');

        if (open.length === 1) {

            publish('app.elevator.in', {
                weight: parseInt(weight)
            });

            return;
        }

        //call elevator
        controllerFloorButton.click();

        input.val(weight);
    }

    function log() {
        console.log.apply(this, arguments);
    }

    function publish(topic, data) {
        if (typeof session === 'undefined') {
            return log('error', 'Connection closed');
        }

        session.publish(topic, data || {});

        log('out', topic, data);
    }

    //int generate random value - min <= value <= max
    function random(min, max) {
        return Math.floor(Math.random() * max) + min;
    }

    return {
        run: function () {
            window.addEventListener('load', function () {
                init();
            }, false);
        }
    }
})().run();