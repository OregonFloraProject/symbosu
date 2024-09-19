import "regenerator-runtime/runtime";

import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom";

function FormElement(props) {
  return (
    <div>
      {props.label}:{' '}
      {props.type === "text" &&
        <input
          type="text"
          value={props.value || ""}
          onChange={e => props.onChange(e.target.value)}
          disabled={props.disabled}
        />
      }
      {props.type === "textarea" &&
        <textarea
          value={props.value || ""}
          onChange={e => props.onChange(e.target.value)}
          disabled={props.disabled}
        />
      }
    </div>
  );
}

function postData(data) {
  const params = new URLSearchParams();
  for (let key in data) {
    params.set(key, data[key]);
  }
  return params.toString();
}

function validateForm({ firstName, lastName, email, title, institution, department, reason }) {
  const validation = {};
  if (!firstName) validation.firstName = { empty: true };
  if (!lastName) validation.lastName = { empty: true };
  if (!email) validation.email = { empty: true };
  if (!title) validation.title = { empty: true };
  if (!institution) validation.institution = { empty: true };
  if (!reason) validation.reason = { empty: true };

  if (email && !email.match(/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/)) {
    validation.email = { invalid: true };
  }
  if (reason && reason.length > 250) {
    validation.reason = { length: true };
  }

  return validation;
}

function PolicyApp(props) {
  const [firstName, setFirstName] = useState();
  const [lastName, setLastName] = useState();
  const [email, setEmail] = useState();
  const [title, setTitle] = useState();
  const [institution, setInstitution] = useState();
  const [department, setDepartment] = useState();
  const [reason, setReason] = useState();

  const [hasRequested, setHasRequested] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [validation, setValidation] = useState({});

  useEffect(() => {
    const fetchInitialData = async () => {
      try {
        const res = await fetch(`${props.clientRoot}/profile/rpc/api.php`);
        if (!res.ok) {
          throw new Error(`Response status: ${res.status}`);
        }
        const data = await res.json();

        if ("hasRequested" in data) {
          setHasRequested(data.hasRequested);
        } else if ("email" in data) {
          setFirstName(data.firstName);
          setLastName(data.lastName);
          setEmail(data.email);
          setTitle(data.title);
          setInstitution(data.institution);
          setDepartment(data.department);
        }
      } catch (e) {
        // TODO(eric): add error handling
        console.error(e);
      } finally {
        setIsLoading(false);
      }
    }

    fetchInitialData();
  }, []);

  const isLoggedIn = !!props.userName;
  return (
    <div className="info-page">
      <section id="titlebackground" className="title-leaf">
        <div className="inner-content">
          <h1>Rare Plant Guide</h1>
        </div>
      </section>
      <section>
        <div className="inner-content">
          <h2>Access Policy</h2>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce egestas sem id lectus ultricies sollicitudin. Aliquam tristique turpis ac ipsum rutrum ornare.</p>
          <p>Sed congue consectetur venenatis. Ut blandit tellus nisi, et rhoncus purus blandit quis. Nunc dignissim quam nisi, id dictum nisl accumsan et. Nullam ut euismod tortor. Suspendisse accumsan erat tortor, sit amet aliquam purus luctus eu. Proin libero nisl, auctor non efficitur at, placerat vel odio.</p>
          <p>Mauris dapibus finibus augue, a posuere mauris consequat non. Duis volutpat fermentum imperdiet. Curabitur nec ex eros. </p>
          {isLoggedIn ?
            (hasRequested ?
              <div>Your request is pending review</div> :
              <div>
                <FormElement
                  disabled={isLoading}
                  type="text"
                  value={firstName}
                  onChange={setFirstName}
                  label="First Name"
                />
                <FormElement
                  disabled={isLoading}
                  type="text"
                  value={lastName}
                  onChange={setLastName}
                  label="Last Name"
                />
                <FormElement
                  disabled={isLoading}
                  type="text"
                  value={email}
                  onChange={setEmail}
                  label="Email"
                />
                <FormElement
                  disabled={isLoading}
                  type="text"
                  value={title}
                  onChange={setTitle}
                  label="Position / Role"
                />
                <FormElement
                  disabled={isLoading}
                  type="text"
                  value={institution}
                  onChange={setInstitution}
                  label="Institution / Agency"
                />
                <FormElement
                  disabled={isLoading}
                  type="text"
                  value={department}
                  onChange={setDepartment}
                  label="Department (optional)"
                />
                <FormElement
                  disabled={isLoading}
                  type="textarea"
                  value={reason}
                  onChange={setReason}
                  label="Reason for requesting access"
                />
                <button
                  className="btn-primary"
                  onClick={async () => {
                    const data = { firstName, lastName, email, title, institution, department, reason };
                    const validation = validateForm(data);
                    if (Object.keys(validation).length) {
                      setValidation(validation);
                      console.error(validation);
                    } else {
                      setIsLoading(true);
                      const res = await fetch(`${props.clientRoot}/profile/rpc/api.php`, {
                        method: "POST",
                        headers: {
                          "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: postData(data),
                      });
                      if (res.ok) {
                        setHasRequested(true);
                        setIsLoading(false);
                      } else {
                        // TODO(eric): handle error
                      }
                      console.log(await res.text());
                    }
                  }}
                >Update profile and submit request</button>
              </div>
            ) :
            <button className="btn-primary" href={`${props.clientRoot}/profile/index.php?refurl=${ location.pathname }`}>Login to request access</button>
          }
        </div>
      </section>
    </div>
  );
}

const headerContainer = document.getElementById("react-header");
const dataProps = JSON.parse(headerContainer.getAttribute("data-props"));
const domContainer = document.getElementById("react-rare-policy");
ReactDOM.render(<PolicyApp clientRoot={ dataProps["clientRoot"] } userName={ dataProps["userName"] } />, domContainer);
