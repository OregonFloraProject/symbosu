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
  [Status.SENT]:
    'Thanks! Your request has been received and will be reviewed by the OregonFlora team. You will receive an email when your request has been processed.',
  [Status.ERROR]: (
    <>
      An error occurred. Please try again later or <a href="../pages/contact.php">contact us</a>.
    </>
  ),
  [Status.ACCESS_REQUESTED]:
    'Your request has been received and is pending review. You will receive an email when your request has been processed.',
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
          <h2>Policy on Sharing of Sensitive Plant Taxon Information</h2>
          <p>
            Access to detailed location data is currently limited to agency workers and researchers, in accordance with
            practices of the organizations responsible for protecting these species. A snapshot of the distribution map
            for each rare taxon is publicly accessible. To request access to sensitive locality data, please ensure you
            have an OregonFlora account, which can be initiated from the &quot;
            <a href={`${props.clientRoot}/profile/index.php`}>Login</a>&quot; link in the website banner. While logged
            in, complete and submit the Sensitive Data Access Request form.
          </p>
          <p>
            Sensitive data is not to be redistributed or shared beyond the requester&apos;s department, agency, or
            business, or for subsequent unrelated work without permission from the data owner. While OregonFlora and its
            data partners make every effort to present accurate and up-to-date information about the taxonomy and
            location of each record, the data are made available &quot;as- is&quot;. Errors or feedback should be
            reported to the appropriate collection manager, which is indicated at the bottom of every occurrence profile
            page.
          </p>
          <p>
            The OregonFlora dataset is dynamic, and we are continuously curating it to reflect current knowledge.
            Consequently, delayed use or use of a static copy of any dataset can result in inaccuracies, and we strongly
            recommend against this practice. Access to sensitive data will expire <b>six months</b> from the granting of
            permissions; an email will be sent as a reminder to update your request according to your data needs.
          </p>
          <p>
            It is a matter of professional ethics to cite and acknowledge the work of other scientists that has resulted
            in data used in subsequent research. We request that you cite both primary data sources and OregonFlora when
            using these data in your reports, papers, and publications. This information helps us demonstrate
            OregonFlora impact when applying for funding to support our program. Here are examples of recommended
            citations:
          </p>
          <ul>
            <li>
              <i>(citing occurrence data from specific institution(s));</i> Biodiversity data published by: &lt;Name(s)
              of Collection&gt;. Accessed via OregonFlora Portal, https://oregonflora.org, YYYY-MM-DD.
              <ul>
                <li>
                  Example: Biodiversity data published by: Oregon State University Vascular Plant Collection,
                  OregonFlora Field Photo, Oregon Biodiversity Information Center, Vascular Plants. Accessed via
                  OregonFlora Portal, https://oregonflora.org, YYYY- MM-DD.
                </li>
                <li>
                  Example (individual record): Oregon State University Vascular Plant Collection, Occurrence ID
                  &#123;F9C31938-5F97-4DCA-92F8-7F35A9CE2050&#125;. Accessed via OregonFlora Portal,
                  https://oregonflora.org, YYYY-MM-DD.
                </li>
              </ul>
            </li>
            <li>
              <i>(citing the rare plant guide)</i> OregonFlora. Rare Plant Guide.
              https://oregonflora.org/portal/rare/&lt;rare plant profile page&gt;, YYYY-MM-DD.
            </li>
          </ul>
          <p className="updated-date">(May 2025)</p>
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
                <h3>Sensitive Data Access Request form</h3>
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
                  Submit Request
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
