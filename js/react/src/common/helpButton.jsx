import React from 'react';

class HelpButton extends React.Component {
  constructor(props) {
    super(props);
    this.getHelpButtonId = this.getHelpButtonId.bind(this);
  }

  getHelpButtonId() {
    return this.props.title.toLowerCase().replace(/[^a-z]/g, '') + '-help';
  }

  componentDidMount() {
    const helpButtonId = this.getHelpButtonId();
    /**
     * TODO(eric): As written, this requires Bootstrap's JS scripts to work, but we really shouldn't
     * be using both Bootstrap JS and React simultaneously. I've removed Bootstrap JS from the site,
     * and nothing is currently using this HelpButton component, but if we want to use it in the
     * future it should be rewritten to use react-bootstrap or some other React library.
     */
    // eslint-disable-next-line no-undef
    $(`#${helpButtonId}`).popover({
      title: this.props.title,
      html: true,
      trigger: 'focus',
      placement: 'bottom',
      content: this.props.html,
    });
  }

  render() {
    return (
      <button id={this.getHelpButtonId()} className="help-button">
        <img style={{ width: '1.25em' }} alt="help" src={`${this.props.clientRoot}/images/garden/help.png`} />
      </button>
    );
  }
}

export default HelpButton;
