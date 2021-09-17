/**
 * Copyright © Volodymyr Klymenko. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'recorder',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function($, Recorder, modal, $t) {

    return function(options) {
        $('#voice-modal').modal({
            type: 'slide',
            title: $t('Found in Store -> Configuration'),
            buttons: [{
                text: $t('Close'),
                'class': 'action-secondary',
                click: function () {
                    this.closeModal();
                }
            }]
        });

        //webkitURL is deprecated but nevertheless
        URL = window.URL || window.webkitURL;

        let gumStream; //stream from getUserMedia()
        let rec; //Recorder.js object
        let input; //MediaStreamAudioSourceNode we'll be recording
        let recordingProcess;

        // shim for AudioContext when it's not avb.
        let AudioContext = window.AudioContext || window.webkitAudioContext;
        let audioContext //audio context to help us record

        let recordButton = document.getElementById("recordButton");
        let voicePhraseLabel = $('.voice-global-phrase');

        //add events to those 2 buttons
        recordButton.addEventListener("click", recording);

        function recording() {
            if (recordingProcess === 'started') {
                stopRecording();
            } else {
                startRecording();
            }
        }

        function startRecording() {
            recordingProcess = 'started';
            voicePhraseLabel.text('');
            console.log("recordButton clicked");

            let constraints = {audio: true, video: false}
            navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
                console.log("getUserMedia() success, stream created, initializing Recorder.js ...");

                audioContext = new AudioContext();

                $('.voice-global-loader').show();

                /*  assign to gumStream for later use  */
                gumStream = stream;

                /* use the stream */
                input = audioContext.createMediaStreamSource(stream);

                rec = new Recorder(input, {numChannels: 1})

                //start the recording process
                rec.record()

                console.log("Recording started");

                setTimeout(stopRecording, 5000);

            }).catch(function (err) {
                //enable the record button if getUserMedia() fails
            });
        }

        function stopRecording() {
            if (recordingProcess === 'stopped') {
                return;
            }

            recordingProcess = 'stopped';

            $('.voice-global-loader').hide();
            console.log("stopButton clicked");

            //tell the recorder to stop the recording
            rec.stop();

            //stop microphone access
            gumStream.getAudioTracks()[0].stop();

            //create the wav blob and pass it on to createDownloadLink
            rec.exportWAV(createDownloadLink);
        }

        function createDownloadLink(blob) {
            let wrapper = function() {
                function onLoaded() {
                    //only use base64-encoded data, i.e. remove meta-data from beginning:
                    let audioData = reader.result.replace(/^data:audio\/wav;base64,/, '');
                    data = {
                        config: {
                            encoding: "LINEAR16",
                            languageCode: 'en-US',
                            maxAlternatives: 20
                        },
                        audio: {
                            content: audioData
                        }
                    };

                    $.post(options.url, {
                        data: data
                    }, function (json) {
                        let element = $('.voice-global-phrase');

                        if (json.redirect !== undefined) {
                            window.location = json.redirect;
                        }

                        switch (json.type) {
                            case 2:
                                let phrase = json.config !== undefined ? json.config.config.phrase : $t('no results');
                                let url = json.config !== undefined ? json.config.config.url : null;

                                element.html(url ? '<a href="' + url + '" title="' + phrase + '">' + phrase + '</a>' : phrase);
                                break;
                            case 3:
                                let html = '<ul>';
                                $.each(json.items, function (index, item) {
                                    html += '<li class="level-0"><a href="' + item.config.url + '" title="' + item.phrase + '">' + item.phrase + '</a></li>';

                                });
                                html += '</ul>'
                                $('#voice-modal-content').html(html);
                                $('#voice-modal').modal('openModal');
                                break;
                            default:
                                element.text($t('no results'));

                        }
                    });
                }

                let reader = new window.FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = onLoaded;
            }

            return wrapper();
        }
    }
});
