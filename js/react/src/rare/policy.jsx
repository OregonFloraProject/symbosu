import React from "react";
import ReactDOM from "react-dom";

const PROFILE_FIELDS = [
  { id: "firstName", label: "First Name", type: "text" },
  { id: "lastName", label: "Last Name", type: "text" },
  { id: "email", label: "Email", type: "text" }, // TODO(eric): may not need this
  { id: "title", label: "Position / Role", type: "text" },
  { id: "institution", label: "Institution / Agency", type: "text" },
  { id: "department", label: "Department (optional)", type: "text" },
  { id: "reason", label: "Reason for requesting access", type: "textarea" },
];

function PolicyApp(props) {
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
            <div>
              {PROFILE_FIELDS.map(field => (
                <div>
                  {field.label}:
                  {field.type === "text" &&
                    <input type="text" />
                  }
                  {field.type === "textarea" &&
                    <textarea />
                }
                </div>
              ))}
              <a className="btn-primary">Update profile and submit request</a>
            </div> :
            <a className="btn-primary" href={`${props.clientRoot}/profile/index.php?refurl=${ location.pathname }`}>Login to request access</a>
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
