import React from 'react';

export class SynonymItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showSynonyms: false,
      //hiddenSynonyms: false,
      maxSynonyms: 3,
    };
  }
  toggleSynonyms = () => {
    this.setState({ showSynonyms: !this.state.showSynonyms });
  };

  render() {
    let visibleItems = this.props.value.slice(0, this.state.maxSynonyms);
    let hiddenItems = this.props.value.slice(this.state.maxSynonyms);

    return (
      <div className={'synonym-items row dashed-border py-1'}>
        <div className="col font-weight-bold char-label">Synonyms and Misapplied Names</div>
        <div className="synonym-list col">
          <span className="short-list">
            {visibleItems.length > 0 &&
              Object.entries(visibleItems)
                .map(([key, obj]) => {
                  return (
                    <span key={key} className={'synonym-item'}>
                      <span className={'synonym-sciname'}>{obj.sciname}</span>
                      <span className={'synonym-author'}> {obj.author}</span>
                    </span>
                  );
                })
                .reduce((prev, curr) => [prev, ', ', curr])}
            {hiddenItems.length > 0 && !this.state.showSynonyms ? '...' : ''}
          </span>

          <span className="full-list" hidden={!this.state.showSynonyms}>
            {hiddenItems.length > 0 &&
              Object.entries(hiddenItems)
                .map(([key, obj]) => {
                  return (
                    <span key={key} className={'synonym-item'}>
                      <span className={'synonym-sciname'}>{obj.sciname}</span>
                      <span className={'synonym-author'}> {obj.author}</span>
                    </span>
                  );
                })
                .reduce((prev, curr) => [prev, ', ', curr])}
          </span>

          {this.props.value.length > this.state.maxSynonyms && (
            <span>
              <div className="up-down-toggle">
                <FontAwesomeIcon
                  icon={this.state.showSynonyms ? 'chevron-up' : 'chevron-down'}
                  onClick={this.toggleSynonyms}
                />
              </div>
            </span>
          )}
        </div>
      </div>
    );
  }
}