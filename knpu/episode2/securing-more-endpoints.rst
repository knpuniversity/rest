Securing More Endpoints
=======================

Hey! We have this great system where users are actually being authenticated!
Now we can start checking for security everywhere we need it. In ``newAction``
we're requiring that you are logged in::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        if (!$this->isUserLoggedIn()) {
            throw new AccessDeniedException();
        }
        // ...
    }

Awesome! In ``showAction`` and ``listAction`` we are going to leave those
anonymous. In ``updateAction``, we *do* need some extra security. It's more
than just being logged in: we need to check to see if our user is actually
the owner of that programmer or not. So we just add some ``if`` statement
logic: ``if ($programmer->userId != $this->getLoggedInUser()->id``, then
``throw new AccessDeniedException``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if ($programmer->userId != $this->getLoggedInUser()->id) {
            throw new AccessDeniedException();
        }

        // ...
    }

Easy enough!

Centralizing Security Logic Checks
----------------------------------

Since we're also going to use this in ``deleteAction`` let's go into our
``BaseController`` and make this a generic function. Open up the ``BaseController``,
create a new protected function ``enforceProgrammerOwnershipSecurity``.
Let's copy the logic in there and don't forget to add your ``AccessDeniedException``
``use`` statement::

    // src/KnpU/CodeBattle/Controller/BaseController.php
    // ...
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    // ...

    protected function enforceProgrammerOwnershipSecurity(Programmer $programmer)
    {
        if ($this->getLoggedInUser()->id != $programmer->userId) {
            throw new AccessDeniedException();
        }
    }

Perfect, so now go back to our ``ProgrammerController``. It's a lot easier
to just reuse this logic. Let's also use this down in ``deleteAction``::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);
        // ...

        $this->enforceProgrammerOwnershipSecurity($programmer);

        // ...
    }

    public function deleteAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);
        // ...

        $this->enforceProgrammerOwnershipSecurity($programmer);

        // ...
    }

Now the only other thing that could go wrong, is if the user is not logged
in at all and they hit ``updateAction``. Then, we would die inside this function.
The problem is that ``$this->getLoggedInUser`` would be null and we'll call
the ``id`` property on a null object::

    // src/KnpU/CodeBattle/Controller/BaseController.php
    // ...

    protected function enforceProgrammerOwnershipSecurity(Programmer $programmer)
    {
        if ($this->getLoggedInUser()->id != $programmer->userId) {
            throw new AccessDeniedException();
        }
    }

So before we call this function, we need to make sure the user is at least 
logged in. If they aren't, then they are definitely not the owner of this programmer.

So let's create another function here called ``enforceUserSecurity``. In
this case, go back to ``ProgrammerController`` and grab the logic right here::

    // src/KnpU/CodeBattle/Controller/BaseController.php
    // ...

    protected function enforceUserSecurity()
    {
        if (!$this->isUserLoggedIn()) {
            throw new AccessDeniedException();
        }
    }

There we go. And from inside ``enforceProgrammerOwnershipSecurity`` we can
just make sure that the user is actually logged in::

    // src/KnpU/CodeBattle/Controller/BaseController.php
    // ...

    protected function enforceProgrammerOwnershipSecurity(Programmer $programmer)
    {
        $this->enforceUserSecurity();

        if ($this->getLoggedInUser()->id != $programmer->userId) {
            throw new AccessDeniedException();
        }
    }

And in ``ProgrammerController``, we can do the same thing and save ourselves
a little bit of code::

    // src/KnpU/CodeBattle/Controller/Api/ProgrammerController.php
    // ...

    public function newAction(Request $request)
    {
        $this->enforceUserSecurity();
        // ...
    }

Between these two new methods, we have a really easy way to go function
by function inside of our controller to make sure that we're enforcing the right
type of security.

Because we're sending our ``Authorization`` header in the background of our
scenarios, we should be able to run our entire ``programmer.feature`` and
see it pass:

.. code-block:: bash

    php vendor/bin/behat features/api/programmer.feature

Perfect! And just like that, we have our entire application locked down.
