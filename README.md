BAN Framework
=============

* A Zend-based framework for creating and consuming ReSTful APIs.
* Uses Json serialisation by default.
* Author: Adam Hayward [adam - at - websitestori - dot - es]
* Web: http://websitestori.es

Introduction
------------

The BAN Framework is designed to make it easy to create APIs that follow the
principles of [Representational State Transfer](http://en.wikipedia.org/wiki/Representational_State_Transfer).

BAN parts from traditional [MVC](http://en.wikipedia.org/wiki/Architectural_pattern_%28computer_science%29)
frameworks by using a three-tier architecture consisting of models, services and DAOs as follows:

* *Models*: define a resource-type or collection of resources, along with their properties, relations and routes.
* *Services*: provides access to resources defined by a model via HTTP verbs: GET, PUT, POST, DELETE and also OPTIONS, HEAD and TRACE.
* *DAOs*: provide access to the underlying data layer, such as a database or message queue.

Example Model
-------------

Unlike many other frameworks, BAN models provide no data access. They simply 
specify:

* Properties belonging to the model
* Relations with other models
* Routes to map URIs to a given model
* Which service will handle HTTP requests

The following model defines a collection of resources called 'articles' and 
allows us to perform database CRUD operations via HTTP. The 'routes' defined 
will map calls to these URLs in our API with this model.

```php
class Example_Article
    extends Ban_Model_Abstract
    implements Ban_Model_Interface
{
    protected $_name = 'article';
    protected $_collection = 'articles';
    protected $_daoClass = 'Ban_Dao_Db';
    protected $_serviceClass = 'Ban_Service_Generic';
    
    public function initProperties()
    {
        $this->addProperty('id', new Ban_Property_AutoId(), array());
        $this->addProperty('title', new Ban_Property_String(), array());
        $this->addProperty('article', new Ban_Property_String(), array());
        $this->addProperty('created', new Ban_Property_Datetime(), array());
        $this->addProperty('updated', new Ban_Property_Datetime(), array());
    }

    public function initRelations()
    {
        $this->addRelation('belongsTo', 'Example_User');
		$this->addRelation('hasMany', 'Example_Comment');
    }

    public function initRoutes()
    {
        $this->addRoute('articles');
        $this->addRoute('articles/*', array('id'));
    }
}
```

Usage
-----

    Fetch a list of all articles:
    GET http://ban-api.example.com/articles

    Fetch a specific article:
    GET http://ban-api.example.com/articles/<id>

    Create a new article:
    POST http://ban-api.example.com/articles

    Update an article:
    PUT http://ban-api.example.com/articles/<id>

    Delete an article:
    DELETE http://ban-api.example.com/articles/<id>

Api Client
----------

BAN comes with a built-in client so you can easily talk to yours APIs.

Usage:

    // Create a client
    $client = new Ban_Client('http://ban-api.example.com/');

    // Fetch a list of all articles:

    $client->get('articles');

    // Fetch a specific article:

    $client->get('articles/' . $id);

    // Create a new article:
    
    $articleData = array('title' => 'New Article', /* etc */);
    $client->post('articles', $articleData);

    // Update an article:
        
    $articleData = array('title' => 'Newer Article');
    $client->put('articles/' . $id, $articleData);

    // Delete an article:
    $client->delete('articles/' . $id);

Api Server Map
--------------

When we visit the 'homepage' (root) of our api, we can get information about
the resources available for our service. For example:

     GET http://ban-api.example.com/

```js
{
  "result": {
    /* Array of namespaces where our models are defined */
    "namespaces": [
      "Plio_Model"
    ],
    
    /* Array of models */
    "models": [
      "article",
      "author"
    ],
    
    /* Array mapping URLs to models */
    "routes": [
      {
        "uri": null,
        "model": "root",
        "params": []
      },
      {
        "uri": "/articles",
        "model": "article",
        "params": []
      },
      {
        "uri": "/articles/*",
        "model": "article",
        "params": [
          "id"
        ]
      },
      {
        "uri": "/authors",
        "model": "author",
        "params": []
      },
      {
        "uri": "/authors/*",
        "model": "author",
        "params": [
          "id"
        ]
      }
  }
}
```

License
-------

    Copyright (c) 2010, Adam Hayward [adam - at - websitestori - dot - es]
    All rights reserved.

    Redistribution and use in source and binary forms, with or 
    without modification, are permitted provided that the following 
    conditions are met:

    * Redistributions of source code must retain the above copyright 
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright 
      notice, this list of conditions and the following disclaimer in the 
      documentation and/or other materials provided with the distribution.
    * Neither the name of the author nor the names of its contributors 
      may be used to endorse or promote products derived from this 
      software without specific prior written permission.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS 
    IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED 
    TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
    PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT 
    HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
    SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT 
    LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
    DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY 
    THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE 
    OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
