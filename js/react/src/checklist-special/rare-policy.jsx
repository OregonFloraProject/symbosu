import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faChevronLeft } from '@fortawesome/free-solid-svg-icons';

library.add(faChevronLeft);

const ValidationMessage = {
  empty: 'required',
  invalid: 'Please enter a valid email address',
  length: 'Must be 250 characters or less',
};

function FormElement(props) {
  return (
    <div className="form-group">
      <label htmlFor={`form-${props.id}`}>{props.label}: </label>
      {props.type === 'text' && (
        <input
          id={`form-${props.id}`}
          className={`form-control${props.validation ? ' is-invalid' : ''}`}
          type="text"
          value={props.value || ''}
          onChange={(e) => props.onChange(e.target.value)}
          disabled={props.disabled}
        />
      )}
      {props.type === 'textarea' && (
        <textarea
          id={`form-${props.id}`}
          className={`form-control${props.validation ? ' is-invalid' : ''}`}
          value={props.value || ''}
          onChange={(e) => props.onChange(e.target.value)}
          disabled={props.disabled}
          maxLength={props.maxLength}
        />
      )}
      {props.validation && (
        <div className="invalid-feedback">{ValidationMessage[Object.keys(props.validation)[0]]}</div>
      )}
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

const HTMLDecodeMap = {
  '&apos;': "'",
  '&quot;': '"',
};
const HTMLDecodeRegex = new RegExp(Object.keys(HTMLDecodeMap).join('|'), 'g');
function decodeHTMLChars(string) {
  // due to Person.php, our api endpoint returns strings with ' and " replaced with &apos; and
  // &quot; respectively
  return string.replace(HTMLDecodeRegex, (match) => HTMLDecodeMap[match]);
}

function validateForm({ firstName, lastName, email, title, institution, reason }) {
  const validation = {};
  if (!firstName) validation.firstName = { empty: true };
  if (!lastName) validation.lastName = { empty: true };
  if (!email) validation.email = { empty: true };
  if (!title) validation.title = { empty: true };
  if (!institution) validation.institution = { empty: true };
  if (!reason) validation.reason = { empty: true };

  if (
    email &&
    !email.match(
      /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/,
    )
  ) {
    validation.email = { invalid: true };
  }
  if (reason && reason.length > 250) {
    validation.reason = { length: true };
  }

  return validation;
}

const Status = {
  LOADING: 'loading',
  SENT: 'sent',
  ERROR: 'error',
  ACCESS_REQUESTED: 'accessRequested',
  ACCESS_GRANTED: 'accessGranted',
  READY: 'ready',
};

const StatusDisplayText = {
  [Status.SENT]: 'Thanks! Your request has been received and will be reviewed by the OregonFlora team.',
  [Status.ERROR]: (
    <>
      An error occurred. Please try again later or <a href="../pages/contact.php">contact us</a>.
    </>
  ),
  [Status.ACCESS_REQUESTED]: 'Your request has been received and is pending review.',
  [Status.ACCESS_GRANTED]: 'You have been granted access by the OregonFlora team.',
};

function PolicyApp(props) {
  const [firstName, setFirstName] = useState();
  const [lastName, setLastName] = useState();
  const [email, setEmail] = useState();
  const [title, setTitle] = useState();
  const [institution, setInstitution] = useState();
  const [department, setDepartment] = useState();
  const [reason, setReason] = useState();

  const [status, setStatus] = useState(Status.LOADING);
  const [validation, setValidation] = useState({});

  const [refetch, setRefetch] = useState(0);
  useEffect(() => {
    const fetchInitialData = async () => {
      try {
        const res = await fetch(`${props.clientRoot}/profile/rpc/api.php`);
        if (!res.ok) {
          throw new Error(`API response status: ${res.status}`);
        }
        const data = await res.json();

        if ('accessRequested' in data) {
          setStatus(Status.ACCESS_REQUESTED);
        } else if ('accessGranted' in data) {
          setStatus(Status.ACCESS_GRANTED);
        } else if ('email' in data) {
          setFirstName(decodeHTMLChars(data.firstName));
          setLastName(decodeHTMLChars(data.lastName));
          setEmail(decodeHTMLChars(data.email));
          setTitle(decodeHTMLChars(data.title));
          setInstitution(decodeHTMLChars(data.institution));
          setDepartment(decodeHTMLChars(data.department));

          setStatus(Status.READY);
        } else if ('error' in data) {
          throw new Error(`API error: ${data.error}`);
        }
      } catch (e) {
        console.error(e);
        setStatus(Status.ERROR);
      }
    };

    fetchInitialData();
  }, [refetch]);

  const isLoggedIn = !!props.userName;
  const isLoading = status === Status.LOADING;
  const shouldShowForm = isLoggedIn && (status === Status.LOADING || status === Status.READY);

  const backToPage = new URLSearchParams(window.location.search).get('refurl') || `${props.clientRoot}/rare/index.php`;

  return (
    <div className="info-page">
      <section id="titlebackground">
        <div className="inner-content">
          <h1>Rare Plant Guide</h1>
        </div>
      </section>
      <section>
        <div className="inner-content mt-4">
          <p>
            <a href={backToPage}>
              <FontAwesomeIcon icon="chevron-left" /> Back
            </a>
          </p>
          <h2>Access Policy</h2>
          <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce egestas sem id lectus ultricies sollicitudin.
            Aliquam tristique turpis ac ipsum rutrum ornare.
          </p>
          <p>
            Sed congue consectetur venenatis. Ut blandit tellus nisi, et rhoncus purus blandit quis. Nunc dignissim quam
            nisi, id dictum nisl accumsan et. Nullam ut euismod tortor. Suspendisse accumsan erat tortor, sit amet
            aliquam purus luctus eu. Proin libero nisl, auctor non efficitur at, placerat vel odio.
          </p>
          <p>
            Mauris dapibus finibus augue, a posuere mauris consequat non. Duis volutpat fermentum imperdiet. Curabitur
            nec ex eros.{' '}
          </p>
          <div className="py-4">
            {!shouldShowForm ? (
              isLoggedIn ? (
                <h5>{StatusDisplayText[status]}</h5>
              ) : (
                <a
                  href={`${props.clientRoot}/profile/index.php?refurl=${encodeURIComponent(window.location.pathname + window.location.search)}`}
                >
                  <button className="btn-primary">Login to request access</button>
                </a>
              )
            ) : (
              <>
                <h5>To request access to restricted data, please fill out the following form:</h5>
                <form>
                  <FormElement
                    disabled={isLoading}
                    type="text"
                    id="firstName"
                    value={firstName}
                    onChange={setFirstName}
                    label="First Name"
                    validation={validation.firstName}
                  />
                  <FormElement
                    disabled={isLoading}
                    type="text"
                    id="lastName"
                    value={lastName}
                    onChange={setLastName}
                    label="Last Name"
                    validation={validation.lastName}
                  />
                  <FormElement
                    disabled={isLoading}
                    type="text"
                    id="email"
                    value={email}
                    onChange={setEmail}
                    label="Email"
                    validation={validation.email}
                  />
                  <FormElement
                    disabled={isLoading}
                    type="text"
                    id="title"
                    value={title}
                    onChange={setTitle}
                    label="Position / Role"
                    validation={validation.title}
                  />
                  <FormElement
                    disabled={isLoading}
                    type="text"
                    id="institution"
                    value={institution}
                    onChange={setInstitution}
                    label="Institution / Agency"
                    validation={validation.institution}
                  />
                  <FormElement
                    disabled={isLoading}
                    type="text"
                    id="department"
                    value={department}
                    onChange={setDepartment}
                    label="Department (optional)"
                    validation={validation.department}
                  />
                  <FormElement
                    disabled={isLoading}
                    type="textarea"
                    maxLength={250}
                    id="reason"
                    value={reason}
                    onChange={setReason}
                    label="Reason for requesting access"
                    validation={validation.reason}
                  />
                </form>
                <button
                  className="btn-primary"
                  disabled={isLoading}
                  onClick={async () => {
                    const data = { firstName, lastName, email, title, institution, department, reason };
                    const validation = validateForm(data);
                    if (Object.keys(validation).length) {
                      setValidation(validation);
                    } else {
                      setStatus(Status.LOADING);
                      try {
                        const res = await fetch(`${props.clientRoot}/profile/rpc/api.php`, {
                          method: 'POST',
                          headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                          },
                          body: postData(data),
                        });
                        if (!res.ok) {
                          throw new Error(`API response status: ${res.status}`);
                        }

                        const resJson = await res.json();
                        if (resJson.error) {
                          throw new Error(`API error: ${resJson.error}`);
                        }

                        setStatus(Status.SENT);
                      } catch (e) {
                        console.error(e);
                        setStatus(Status.ERROR);
                      }
                    }
                  }}
                >
                  Update profile and submit request
                </button>
              </>
            )}
            {(status === Status.ACCESS_REQUESTED || status === Status.SENT) && (
              <button
                className="btn-primary"
                onClick={async () => {
                  await fetch(`${props.clientRoot}/profile/rpc/api.php`, {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: postData({ delete: true }),
                  });
                  setRefetch(refetch + 1);
                }}
              >
                Delete request (testing only)
              </button>
            )}
          </div>
        </div>
      </section>
    </div>
  );
}

const headerContainer = document.getElementById('react-header');
const dataProps = JSON.parse(headerContainer.getAttribute('data-props'));
const domContainer = document.getElementById('react-rare-policy');
ReactDOM.render(<PolicyApp clientRoot={dataProps['clientRoot']} userName={dataProps['userName']} />, domContainer);
