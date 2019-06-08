Simple Symfony Chat
========================

**Requirements:**
- PHP >= 7.1
- MySQL
- Enabled JavaScript
- Enabled Cookies

**Features:**
- Multiple channels
- Online users list
- Emoticons
- Clickable links
- Possibility to delete messages for moderator/admin
- Admin Panel to promote/demote users
- Notifications when new message appears
- Youtube iframe for play 
- BBCode

**Installation:**
git clone https://github.com/demotywatorking/Simple-Symfony-Chat.git

composer install

php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force

**Live Demo:**
- https://chatdemo.tk/

username: demo

password: demo

**Bitbucket:**

If you want to see more development details like branches, pull requests:

https://bitbucket.org/basinskiwojciech/chat/src/master/
