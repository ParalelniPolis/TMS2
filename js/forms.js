//TODO učesat kontroly zadání + překlady
//TODO add something like "doing loging/registering, pls wait..."
//TODO dotáhnout kontrolu šesti znaků (u všech funkcí)
function formhash(form, password) {

    //kdyby nefungovalo HTML5 forms
    /*if (password.value.length < 1) {
     alert('Vyplň prosím heslo!');
     form.password.focus();
     return false;
     }*/

    // Create a new element input, this will be our hashed password field.
    var p = document.createElement("input");

    // Add the new element to our form.
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);

    // Make sure the plaintext password doesn't get sent.
    password.value = "password";
    form.password.setAttribute('disabled', 'disabled');
    // Finally submit the form.
    //form.submit();
    //return true;
}
function changeformhash(form, password) {
    var p = document.createElement("input");
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);
    password.value = "password";

    //form.submit();
    //return true;
}

function regformhash(form, password, pComfirmation) {
    // Check that the password is sufficiently long (min 6 chars)
    // The check is duplicated below, but this is included to give more
    // specific guidance to the user
    //kdyby nefungovalo HTML5 forms
    if (password.value.length < 6) {
        alert('Heslo musí být alespoň 6 znaků dlouhé!');
        form.password.focus();
        return false;
    }
    // Check password and confirmation are the same
    if (password.value != pComfirmation.value) {
        alert('Nesouhlasí heslo a potvrzení hesla!');
        form.password.focus();
        return false;
    }
    // Create a new element input, this will be our hashed password field.
    var p = document.createElement("input");

    // Add the new element to our form.
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);

    // Make sure the plaintext password doesn't get sent.
    password.value = "password";
    pComfirmation.value = "password";
    form.password.setAttribute('disabled', 'disabled');
    form.pComfirmation.setAttribute('disabled', 'disabled');

    // Finally submit the form.
    //form.submit();
    //return true;
}

