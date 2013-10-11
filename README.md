Campusnet Authentication (Wordpress plugin)
===========================

Use your universities CampusNet login via their API. It requires a user to be a member of a specific CampusNet group 
(defineable) to get access, that way, the administration of users is kept in one place (or, use group/elementid 0 
to not use a group). For each user logging in, it makes sure that a wordpress user is created/exists, so if the 
CampuseNet API ever breaks, you can disable it and the users can login using the username and password they used 
earlier. 

NOTE: since it tries to detect if a user is a student by checking if the first letter is s, you cannot have 
normal users with usernames that start with s.
