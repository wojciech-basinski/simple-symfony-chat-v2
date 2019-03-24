//source: http://dumpsite.com/forum/index.php?topic=4.msg8#msg8
String.prototype.replaceAll = function (str1, str2, ignore) {
    return this.replace(new RegExp(str1.replace(/([\/\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, "\\$&"), (ignore ? "gi" : "g")), (typeof(str2) == "string") ? str2.replace(/\$/g, "$$$$") : str2);
};
String.prototype.insert = function (index, string) {
    if (index > 0)
        return this.substring(0, index) + string + this.substring(index, this.length);
    else
        return string + this;
};

$(document).ready(function () {
    var settings = {
        'scroll': 1,
        'sound': 1
    };

    Notification.requestPermission();
    $('[data-toggle="tooltip"]').tooltip();

    var removeLine;
    var active = true;
    var newMessagesCount = 0;
    var title = document.title;
    var channelChanged = 0;
    var usersOnline = [];
    var channelsOnChat = [];
    var messagesOnChannel = [];

    window.onblur = function () {
        active = false;
        removeLineNewMessages();
        clearTimeout(removeLine);
    };
    window.onfocus = function () {
        active = true;
        newMessagesCount = 0;
        document.title = title;
        removeLine = setTimeout(removeLineNewMessages, 8000);
    };

    statusInProgress();
    startChat();

    //insert private message text in message-text%
    $(document).on('click', '.online-user, .icon-mail', function () {
        let value = $(this).attr('data-value');
        $('#message-text').val('/priv ' + value + ' ').focus();
    });

    $(document).on('click', '.youtube', function () {
        $('.youtube-video-iframe').attr('src', $(this).attr('data-href'));
        $('.youtube-video').css('display', 'flex').draggable();//.resizable();
    });

    $('.youtube-video').on('click', '.close', function () {
        $('.youtube-video-iframe').attr('src', '');
        $('.youtube-video').css('display', 'none');
    });

    $(document).on('click', '#scroll', function () {
        changeScroll()
    });

    function changeScroll() {
        if (settings.scroll === 1) {
            settings.scroll = 0;
            $('#scroll').removeClass('scroll').addClass('no-scroll');
        } else {
            settings.scroll = 1;
            $('#scroll').removeClass('no-scroll').addClass('scroll');
        }
        setSettingsToLocalStorage();
    }

    $(document).on('click', '#sound', function () {
        changeSound()
    });

    $("#emoji-container").emojioneArea({
        standalone: true,
        search: false,
        filtersPosition: "bottom",
        shortnames: true,
        events: {
            emojibtn_click: function (button, event) {
                addText(button.attr("data-name"));
                return false;
            }
        }
    });


    function changeSound() {
        if (settings.sound === 1) {
            settings.sound = 0;
            $('#sound').removeClass('audio-on').addClass('audio-off');
        } else {
            settings.sound = 1;
            $('#sound').removeClass('audio-off').addClass('audio-on');
        }
        setSettingsToLocalStorage();
    }

    //sending new message when clicked on button
    $('body').on('click', '#send', function () {
        sendMessage();
    });

    //sending new message when pressed enter
    $('body').on('keypress', '#message-text', function (event) {
        if (event.which == 13 && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    $('body').on('mouseenter', ".message", function () {
        let id = $(this).parent('div[data-id]').attr('data-id');
        if (id === undefined) {
            id = $(this).parent().parent('div[data-id]').attr('data-id');
        }
        $(this).prepend(prependMenu(id));
    });

    function prependMenu(id) {
        return '<div class="relative-for-menu"><div class="menu" data-menu-id="' + id + '">'
            + '<span class="quote pointer">' + chatText['quote'] + '</span>'
            + '</div></div>';
    }

    $('body').on('click', '.quote', function () {
        let id = $(this).parent('div.menu').attr('data-menu-id');
        let mainDiv = $('div[data-id="' + id + '"]');
        let messageDiv = mainDiv.children('div.message');
        if (!messageDiv.length) {
            messageDiv = mainDiv.children('div').children('div.message');
        }
        let text = messageDiv.clone().children('span').remove().end().children('div').remove().end().text();
        let author = mainDiv.parent('div.group-messages').find('div div div.presentation span.nick').text();
        addText('@' + author + ': [quote]' + text + '[/quote]\n');
    });

    $('body').on('mouseleave', ".message", function () {
        $(this).find('div.relative-for-menu').remove();
    });

    $('body').on('click', '.channel', function () {
        changeChannel($(this).attr('data-value'));
    });

    $('body').on('click', '.language', function () {
        changeLocale($(this).attr('data-value'));
    });

    $('.emoticon-img').click(function () {
        var emoticon = $(this).attr('alt');
        insertText(emoticon, '');
    });

    $('body').on('click', '.nick', function () {
        insertNick("@" + $(this).text() + ' ');
    });

    $('#messages-box').on('click', '.bbcode-img', function () {
        openInNewTab($(this).attr('src'));
    });

    function openInNewTab(url) {
        window.open(url, '_blank');
    }

    function insertNick(nick) {
        var value = $('#message-text').val();
        $('#message-text').val(value + nick);
        $('#message-text').focus();
    }

    function sendMessage() {
        statusInProgress();
        var text = $('#message-text').val();
        if (text === '') {
            return;
        }
        var params = {
            'text': text
        };
        $('#message-text').val("");
        $('#message-text').focus();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: sendPath,
            data: params
        }).done(function (json) {
            if (json.status === "false") {
                $('#messages-box').append('<div class="message-error">An error occurred while sending message.</div>');
            } else {
                insertSentMessage(json);
                playSound(sendMessageSound);
            }
            if (json.messages) {
                $.each(json.messages, function (key, val) {
                    createNewMessage(val);
                });
            }
            statusOk();
            setTimeout(scrollMessages, 100);
        });
    }

    function checkIfMessageHaveNick(text) {
        return text.search("@" + self.username) !== -1
    }

    function kickFromChannel() {
        channelChanged = 1;
        clearChat();
    }

    function isUserTyping() {
        let value = $('#message-text').val();
        if (value && value.search('/priv') === -1) {
            return 1;
        }
        return 0;
    }

    function addLineNewMessages() {
        if (!active && !newMessagesCount) {
            $('#messages-box .group-messages:last-child').append('<div class="line" data-content="' + chatText.new + '"></div>');
        }
    }

    function refreshUsersOnline(users) {
        if (usersOnline.length) {
            checkIfUserLogout(users);
        }
        addUsersOnline(users);
        refreshUsersOnlineBox(users);
    }

    function refreshChannels(channels) {
        if (!channelsOnChat.length) {
            checkChannels(channels);
        }
        addChannels(channels);
    }

    function checkChannels(channels) {
        channelsOnChat.forEach(function (element, index, array) {
            let inArray = 0;
            Object.keys(channels).forEach(function (key) {
                if (element.key == key) {
                    inArray = 1;
                }
            });
            if (!inArray) {
                $('.channel[data-value="' + element + '"]').remove();
                array.splice(index, 1);
                messagesOnChannel.splice(index, 1);
            }
        });
    }

    function addChannels(channels) {
        Object.keys(channels).forEach(function (key) {
            if (channelsOnChat.indexOf(key) === -1) {
                $('#channels').append(
                    '<div class="text-in-info channel ' + (self.channel == key ? 'active' : '') + '" data-value="' + key + '">' + channels[key] + '</div>'
                );
                channelsOnChat.push(key);
                messagesOnChannel.push(0);
            }
        });
    }

    function addInfoToChannelCountOfMessage(val) {
        channelsOnChat.forEach(function (element, index) {
            if (element == val.channel) {
                addCounterToMessageOnOtherChannels(index, element);
            }
        });
    }

    function addCounterToMessageOnOtherChannels(index, key) {
        messagesOnChannel[index]++;
        let channel = $('.channel[data-value=' + key + ']');
        let value = channel.text();
        if (messagesOnChannel[index] > 1) {
            let indexOf = value.indexOf("(");
            value = value.substr(0, indexOf);
            channel.text(value + ' (' + messagesOnChannel[index] + ')');
        } else {
            channel.text(value + ' (1)');
        }
    }

    function refreshUsersOnlineBox(users) {
        users.forEach(function (element) {
            var value = $('.online-user[data-value="' + element.username + '"]').text();
            if (element.typing) {
                if (value.indexOf("(") === -1) {
                    $('.online-user[data-value="' + element.username + '"]').text(element.username + ' (...)');
                }
            } else {
                if (value.indexOf("(") !== -1) {
                    $('.online-user[data-value="' + element.username + '"]').text(element.username);
                }
            }
        });
    }

    function addUsersOnline(users) {
        users.forEach(function (element) {
            if (usersOnline.indexOf(element.username) === -1) {
                $('#users-box').append(
                    '<div class="' + element.user_role + ' text-in-info online-user" data-value="' + element.username + '">' + element.username + '</div>'
                );
                usersOnline.push(element.username);
            }
        });
    }

    function checkIfUserLogout(users) {
        usersOnline.forEach(function (element, index, array) {
            let inArray = 0;
            users.forEach(function (element1) {
                if (element === element1.username) {
                    inArray = 1;
                }
            });
            if (!inArray) {
                $('.online-user[data-value="' + element + '"]').remove();
                array.splice(index, 1);
            }
        });
    }

    function statusInProgress() {
        $('#status').removeClass('ok').addClass('in-progress');
    }

    function statusOk() {
        $('#status').removeClass('in-progress').addClass('ok');
    }

    function refreshChat() {
        statusInProgress();
        var params = {
            typing: isUserTyping()
        };
        $.ajax({
            method: "POST",
            dataType: "json",
            data: params,
            url: refreshPath
        }).done(function (msg) {
            if (msg[0] === "banned") {
                location.reload(true);
            }
            if (msg.kickFromChannel === 1) {
                kickFromChannel();
            }
            if (msg.channels) {
                refreshChannels(msg.channels);
            }
            if (msg.messages[0]) {
                let addNewLine = false;
                $.each(msg.messages, function (key, val) {
                    if (val.channel === self.channel) {
                        addNewLine = true;
                    }
                });
                if (addNewLine) {
                    addLineNewMessages();
                }
                $.each(msg.messages, function (key, val) {
                    let newMessages = 0;
                    if (val.text == 'delete') {
                        $('div[data-id="' + val.id + '"]').remove();
                    } else {
                        if (val.channel == self.channel || val.channel == self.privateChannelId) {
                            newMessages++;
                            createNewMessage(val);
                        } else {
                            addInfoToChannelCountOfMessage(val);
                        }
                        if (newMessages) {
                            notification(val);
                        }
                        if (channelChanged === 0) {
                            playSound(newMessageSound);
                        }
                    }
                });
                scrollMessages();
            }
            if (msg.usersOnline) {
                refreshUsersOnline(msg.usersOnline);
            }
            if (channelChanged === 1) {
                channelChanged = 0;
            }
            statusOk();
        });
        setTimeout(refreshChat, 1500);
    }

    function createDate(dateInput) {
        var d;
        if (dateInput !== undefined) {
            d = new Date(dateInput);
        } else {
            d = new Date();
        }
        return d;
    }

    function createNewMessage(val) {
        let lastUserId = $('#messages-box .group-messages:last-child').attr('data-user-id');
        var d = createDate(val.date.date);
        var del = '';
        if (self.role === 'administrator' || self.role === 'moderator' || self.role === 'elders' || self.role === 'demotywatorking') {
            del = '<span class="pull-right kursor" data-id="' + val.id + '">&times;</span>';
        }
        var pm = val.privateMessage ? 'private-message' : '';
        var pwMail = (val.username === self.username) ? '' : '<span class="icon-mail pointer" data-value="' + val.username + '"></span>';
        if (parseInt(lastUserId) === val.user_id) {
            insertMessageSameUser(val, pm, del, d);
        } else {
            insertMessageNewUser(val, pm, del, d, pwMail);
        }
        if (!active && !channelChanged) {
            newMessagesCount++;
            document.title = '(' + newMessagesCount + ') ' + title;
        }
        $('[data-toggle="tooltip"]').tooltip();
    }

    function insertSentMessage(msg) {
        msg.date = createDate();
        msg.username = msg.userName;
        msg.user_id = self.user_id;
        if (msg.username === "BOT") {
            msg.user_id = botId;
        }
        msg.user_role = self.role;
        msg.user_avatar = msg.avatar;
        createNewMessage(msg);
    }

    function insertMessageNewUser(val, pm, del, d, pwMail) {
        let avatarUrl = getAvatarUrl(val.user_avatar);
        $('#messages-box').append(
            '<div class="group-messages" data-user-id="' + val.user_id + '">' +
            '<div data-id="' + val.id + '" data-user-id="' + val.user_id + '">' +
            '<img class="avatar" src="' + avatarUrl + '" /> ' +
            '<div><div class="presentation">' +
            pwMail +
            '<span class="' + val.user_role + ' text-bold nick pointer">' + val.username + '</span> ' +
            '<span class="date" data-toggle="tooltip" data-title="(' + dateGetHours(d) + ':' + dateGetMinutes(d) + ':' + dateGetSeconds(d) + ')">(' + dateGetHours(d) + ':' + dateGetMinutes(d) + ')</span>' +
            '</div>' +
            '<div class="message message-text padding-left">' + parseMessage(val.text, pm) + del + '</div>' +
            '</div>' +
            '<div class="clearfix"></div>' +
            '</div>' +
            '</div>'
        );
    }

    function insertMessageSameUser(val, pm, del, d) {
        $('#messages-box .group-messages:last-child').append(
            '<div data-id="' + val.id + '" data-user-id="' + val.user_id + '">' +
            '<div class="date-hidden" data-toggle="tooltip" data-title="(' + dateGetHours(d) + ':' + dateGetMinutes(d) + ':' + dateGetSeconds(d) + ')">' + dateGetHours(d) + ':' + dateGetMinutes(d) + '</div>' +
            '<div class="message message-text padding-left">' + parseMessage(val.text, pm) + del + '</div>' +
            '<div class="clearfix"></div></div>'
        );
    }

    function getAvatarUrl(url) {
        if (url.search('http') !== -1) {
            return url;
        }
        if (url !== '' && url !== null && url !== undefined) {
            return 'https://phs-phsa.ga/download/file.php?avatar=' + url;
        }
        return 'https://t3.ftcdn.net/jpg/00/68/94/42/240_F_68944229_6IIpPh1zjgZuNKBu0emQazU0CUSRACY7.jpg';
    }

    function dateGetHours(date) {
        return ('0' + date.getHours()).slice(-2);
    }

    function dateGetMinutes(date) {
        return ('0' + date.getMinutes()).slice(-2);
    }

    function dateGetSeconds(date) {
        return ('0' + date.getSeconds()).slice(-2);
    }

    function scrollMessages() {
        if (settings.scroll === 1) {
            $('#messages-box').scrollTo('100%')
        }
    }

    function changeChannel(channelId) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: changeChannelPath,
            data: {'channel': channelId}
        }).done(function (msg) {
            if (msg === true) {
                // $('.channel').removeClass('active');
                // $('.channel[data-value="' + channelId + '"]').addClass('active');
                channelChanged = 1;
                self.channel = channelId;
                clearChat();
            }
        });
    }

    function clearChat() {
        usersOnline = [];
        channelsOnChat = [];
        messagesOnChannel = [];
        $('#users-box').empty();
        $('#messages-box').empty();
        $('#channels').empty();
    }

    function initializeSettings() {
        if (localStorage.getItem('settings') === null) {
            setSettingsToLocalStorage();
        } else {
            settings = getSettingsToLocalStorage();
        }
        setSoundDiv();
        setScrollDiv();
    }

    function setSoundDiv() {
        if (settings.sound) {
            $('#sound').addClass('audio-on');
        } else {
            $('#sound').addClass('audio-off');
        }
    }

    function setScrollDiv() {
        if (settings.scroll) {
            $('#scroll').addClass('scroll');
        } else {
            $('#scroll').addClass('no-scroll');
        }
    }

    function setSettingsToLocalStorage() {
        localStorage.setItem('settings', JSON.stringify(settings));
    }

    function getSettingsToLocalStorage() {
        return JSON.parse(localStorage.getItem('settings'));
    }

    function startChat() {
        initializeSettings();
        $.ajax({
            method: "POST",
            dataType: "json",
            data: {typing: 0, chatIndex: 1},
            url: refreshPath
        }).done(function (msg) {
            refreshChannels(msg.channels);
            if (msg.messages[0]) {
                $.each(msg.messages, function (key, val) {
                    if (val.text === 'delete') {
                        $('div[data-id="' + val.id + '"]').remove();
                    } else {
                        createNewMessage(val);
                    }
                });
                setTimeout(scrollMessages, 100);
            }
            if (msg.usersOnline) {
                refreshUsersOnline(msg.usersOnline);
            }
            scrollMessages();
            statusOk();
        });
        for (i = 0; i < emoticonsImg.length; i++) {
            $('div[name="emoticons"]').append(function () {
                if (Array.isArray(emoticons[i])) {
                    alt = emoticons[i][0];
                } else {
                    alt = emoticons[i];
                }
                return '<img src="' + emoticonsImg[i] + '" class="emoticon-img kursor" alt="' + alt + '"/>';
            });
        }
        setTimeout(refreshChat, 1500);
    }

    function parseBbCode(message) {
        if (message.indexOf('[b]') !== -1 && message.indexOf('[/b]') !== -1) {
            let regex = /\[b\]/g;
            let regex2 = /\[\/b\]/g;
            message = messageReplace(message, regex, '<span class="text-bold">');
            message = messageReplace(message, regex2, '</span>');
        }
        if (message.indexOf('[i]') !== -1 && message.indexOf('[/i]') !== -1) {
            let regex = /\[i\]/g;
            let regex2 = /\[\/i\]/g;
            message = messageReplace(message, regex, '<span class="text-italic">');
            message = messageReplace(message, regex2, '</span>');
        }
        if (message.indexOf('[u]') !== -1 && message.indexOf('[/u]') !== -1) {
            let regex = /\[u\]/g;
            let regex2 = /\[\/u\]/g;
            message = messageReplace(message, regex, '<span class="text-underline">');
            message = messageReplace(message, regex2, '</span>');
        }
        if (message.indexOf('[quote]') !== -1 && message.indexOf('[/quote]') !== -1) {
            let regex = /\[quote\]/g;
            let regex2 = /\[\/quote\]/g;
            message = messageReplace(message, regex, '<q>');
            message = messageReplace(message, regex2, '</q>');
        }
        if (message.indexOf('[code]') !== -1 && message.indexOf('[/code]') !== -1) {
            let regex = /\[code\]/g;
            let regex2 = /\[\/code\]/g;
            message = messageReplace(message, regex, '<code>');
            message = messageReplace(message, regex2, '</code>');
        }
        if (message.indexOf('[url]') !== -1 && message.indexOf('[/url]') !== -1) {
            let regex = '[url]';
            let regex2 = '[/url]';
            let start = message.indexOf('[url]') + "[url]".length;
            let end = message.indexOf('[/url]') - "[/url]".length + 1;
            let text = message.substr(start, end);
            message = messageReplace(message, regex + text + regex2, '<a href="' + text + '" target="_blank">' + text + '</a>');
        }
        if (message.indexOf('[img]') !== -1 && message.indexOf('[/img]') !== -1) {
            let regex = '[img]';
            let regex2 = '[/img]';
            let start = message.indexOf('[img]') + "[img]".length;
            let end = message.indexOf('[/img]') - "[/img]".length + 1;
            let text = message.substr(start, end);
            let link = 'https://phs-phsa.ga/chat/img/?url=' + encodeURI(text);
            message = messageReplace(message, regex + text + regex2, '<img class="bbcode-img pointer" src="' + link + '" alt="' + text + '"/>')
        }
        if (message.indexOf('[yt]') !== -1 && message.indexOf('[/yt]') !== -1) {//todo
            let regex = '[yt]';
            let regex2 = '[/yt]';
            let start = message.indexOf('[yt]') + "[yt]".length;
            let end = message.indexOf('[/yt]') - "[/yt]".length + 1;
            let text = message.substr(start, end);
            let replaceText;
            if (text.indexOf("youtube.com/watch?v=") > -1) {
                replaceText = transformLinkYT(text, "v=");
            } else if (text.indexOf("youtu.be/") > -1) {
                replaceText = transformLinkYT(text, "u.be/");
            }
            message = messageReplace(message, regex + text + regex2, replaceText);
        }
        return message;
    }

    function transformLinkYT(text, method) {
        let youtubeArray = text.split(method);
        //for now the youtube id is 11 character(64 bit), not likely to change anytime soon(hope not)
        let youtubeId = youtubeArray[1].substring(0, 11);
        // youtubeClass = 'class="youtubeThis" rel="youtubeThis"';
        let youtubeHref = 'https://www.youtube.com/embed/' + youtubeId + '?rel=0'; //final href with rel=0 parameter, no pesty related videos at the end of our chat video
        return '<img class="youtube pointer" data-href="' + youtubeHref + '" src="https://img.youtube.com/vi/' + youtubeId + '/hqdefault.jpg" />';
    }

    function messageReplace(message, whatReplace, replace) {
        return message.replace(whatReplace, replace);
    }

    function parseMessage(message, pm) {
        if (
            (message.indexOf('[yt]') !== -1 && message.indexOf('[/yt]') !== -1) ||
            (message.indexOf('[img]') !== -1 && message.indexOf('[/img]') !== -1) ||
            (message.indexOf('[url]') !== -1 && message.indexOf('[/url]') !== -1)
        ) {
        } else {
            message = parseLinks(message);
        }
        message = messageCreateParts(message);
        if (checkIfMessageHaveNick(message)) {
            let insertLightSpan = '<span class="light">';
            let start = message.search("@" + self.username);
            let end = start + 1 + self.username.length + insertLightSpan.length;
            message = message.insert(start, insertLightSpan).insert(end, '</span>');
        }
        if (pm === 'private-message') {
            let insertPwClass = '<span class="private-message">';
            let start = 0;
            let end = start + 4 + insertPwClass.length;
            message = message.insert(start, insertPwClass).insert(end, '</span>');
        }
        return message;
    }

    function messageCreateParts(message) {
        var parts = [];
        while (message.indexOf('[yt]') !== -1) {
            let start = message.indexOf('[yt]');
            let end = message.indexOf('[/yt]') + '[/yt]'.length;
            let first = message.substr(0, start);
            let second = message.substr(start, end - start);
            parts.push(first);
            parts.push(second);
            message = message.substr(end, message.length - 1);
        }
        while (message.indexOf('[img]') !== -1) {
            let start = message.indexOf('[img]');
            let end = message.indexOf('[/img]') + '[/img]'.length;
            let first = message.substr(0, start);
            let second = message.substr(start, end - start);
            parts.push(first);
            parts.push(second);
            message = message.substr(end, message.length - 1);
        }
        while (message.indexOf('[url]') !== -1) {
            let start = message.indexOf('[url]');
            let end = message.indexOf('[/url]') + '[/url]'.length;
            let first = message.substr(0, start);
            let second = message.substr(start, end - start);
            parts.push(first);
            parts.push(second);
            message = message.substr(end, message.length - 1);
        }
        if (message.length) {
            parts.push(message);
        }
        parts.forEach(function (value, index, parts) {
            if (
                (value.indexOf('[yt]') !== -1 && value.indexOf('[/yt]') !== -1) ||
                (value.indexOf('[img]') !== -1 && value.indexOf('[/img]') !== -1) ||
                (value.indexOf('[url]') !== -1 && value.indexOf('[/url]') !== -1)
            ) {
                parts[index] = parseBbCode(value);
            } else {
                parts[index] = parseEmoticons(parseBbCode(value));
            }
        });
        if (!parts.length) {
            return parseLinks(parseEmoticons(message));
        }
        return parts.join('');
    }

    function onlyUnique(value, index, self) {
        return self.indexOf(value) === index;
    }

    function createUrlEmoji(text) {
        return 'https://cdn.jsdelivr.net/emojione/assets/3.1/png/32/' + text + '.png';
    }

    function parseEmoticons(message) {
        let reg = /:{1}[a-zA-Z0-9_-]{1,}:{1}/g;
        if (reg.test(message)) {
            let matched = message.match(reg).filter(onlyUnique);
            for (i = 0; i < matched.length; i++) {
                let emojiUrlPart = emoticonsEmoji[matched[i]];
                if (emojiUrlPart === undefined) {
                    continue;
                }
                message = message.replaceAll(matched[i],
                    '<img class="emoticon-text" src="' + createUrlEmoji(emojiUrlPart) + '" alt="' + matched[i] + '"/>'
                    );
            }
        }
        for (i = 0; i < emoticons.length; i++) {
            if (Array.isArray(emoticons[i])) {
                for (j = 0; j < emoticons[i].length; j++) {
                    if (message.includes(emoticons[i][j])) {
                        message = message.replaceAll(emoticons[i][j], '<img class="emoticon-text" src="' + emoticonsImg[i] + '" alt="' + emoticons[i][j] + '"/>');
                    }
                }
            } else {
                if (message.includes(emoticons[i])) {
                    message = message.replaceAll(emoticons[i], '<img class="emoticon-text" src="' + emoticonsImg[i] + '" alt="' + emoticons[i] + '"/>');
                }
            }
        }
        return message;
    }

    //https://stackoverflow.com/a/3890175/6912075
    function parseLinks(inputText) {
        if (inputText.search("https://phs-phsa.ga/chat/img/") !== -1) {
            return inputText;
        }
        var replacedText, replacePattern1;
        if (inputText.indexOf("youtube.com/watch?v=") > -1) {
            replacedText = transformYoutube(inputText);
        } else if (inputText.indexOf("youtu.be/") > -1) {
            replacedText = transformYoutube(inputText);
        } else {
            replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
            replacedText = inputText.replace(replacePattern1, '[url]$1[/url]');
        }

        return replacedText;
    }

    function transformYoutube(inputText) {
        var replacePattern1 = /(\b(https?):\/\/\b(www\.?)youtu[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
        return inputText.replace(replacePattern1, '[yt]$1[/yt]');
    }

    function changeLocale(locale) {
        window.location = languagePath[locale];
    }

    function notification(text) {
        if (Notification.permission === "granted" && !active && !channelChanged) {
            // If it's okay let's create a notification
            var username = text.username;
            var messageText = text.text;

            var notification = new Notification(username, {'body': messageText});
            setTimeout(notification.close.bind(notification), 5000);
        }
    }

    function removeLineNewMessages() {
        if ($('.line').length) {
            $('.line').remove();
        }
    }

    $('#bbcode').on('click', '.btn', function () {
        var bbCode = $(this).attr('data-bbcode');

        insertBBCode(bbCode);
    });

    function insertBBCode(bbCode) {
        var bbCodeFirst = '[' + bbCode + ']';
        var bbCodeSecond = '[/' + bbCode + ']';

        insertText(bbCodeFirst, bbCodeSecond);
    }

    function addText(text) {
        var value = $('#message-text').val();
        $('#message-text').val(value + text).focus();
    }

    function insertText(textStart, textEnd) {
        var selectionStart = $('#message-text').prop('selectionStart');
        var selectionEnd = $('#message-text').prop('selectionEnd');
        var value = $('#message-text').val();

        var pos;
        if (selectionEnd - selectionStart) {
            value = value.insert(selectionEnd, textEnd);
            value = value.insert(selectionStart, textStart);
            pos = selectionStart + textStart.length + selectionEnd - selectionStart + textEnd.length;
        } else {
            value = value.insert(selectionStart, textEnd);
            value = value.insert(selectionStart, textStart);
            pos = selectionStart + textStart.length;
        }

        $('#message-text').focus().val(value).prop('selectionStart', pos).prop('selectionEnd', pos);
    }

    function playSound(sound) {
        if (settings.sound === 1) {
            var audio = new Audio(sound);
            audio.currentTime = 0;
            audio.play();
        }
    }

    // $('#messages-box').on('mouseenter', '.message', function() {
    //     $(this).find('.date-hidden').fadeIn("fast");
    // });
    //
    // $('#messages-box').on('mouseleave', '.message', function() {
    //     $(this).find('.date-hidden').fadeOut("fast");
    // });
});