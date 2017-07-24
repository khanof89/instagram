Laravel Wrapper for Instagram
===================


This is a simple package to provide you all the instagram API accessible at one place. While writing this package I had php7.0 installed but can work with php5.6 as well just change the guzzle version. 

----------


Requirements
-------------

 - Instagram app
 - Instagram Client Id
 - Instagram Client secret

Once you have obtained your instagram client id place it in your .env file as: 

 - INSTAGRAM_CLIENT_ID=
 - INSTAGRAM_REDIRECT_URI= 
 - INSTAGRAM_CLIENT_SECRET= 

once done that access the function by Instagram::functionName($params);

Functions
---------
To auth with this package use auth() function from your route

```php
Route::get('instagram-auth', function(){
    return redirect()->away(\Shahrukh\Instagram\Instagram::auth());
});
```

List of functions available
---------------------------

```php
exchangeCodeForToken() // expects the code you received while authenticating, and gives you access token in return 

getSelf() // gives information about logged in user

getSelfMedia() // gives media list of logged in user

getFollowedBy() // expects access token and user id(default is self), return the list of users who follows the user in question

getFollows() // expects access token and user id(default is self), return the list of users, user in question follows

getFollowRequest() // expects access token and user id (default is self), return the follow requests user in quesiton received
```