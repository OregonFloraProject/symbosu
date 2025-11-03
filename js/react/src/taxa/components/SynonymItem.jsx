import React from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

export class SynonymItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showSynonyms: false,
      showMisappliedNames: false,
      maxSynonyms: 3,
    };
  }
  toggleSynonyms = () => {
    this.setState({ showSynonyms: !this.state.showSynonyms });
  };
  toggleMisappliedNames = () => {
    this.setState({ showMisappliedNames: !this.state.showMisappliedNames });
  };

  render() {
    const synonyms = [];
    const misappliedNames = [];
    this.props.value.forEach(synonym => {
      if (synonym.notes === 'misapplied') {
        misappliedNames.push(synonym);
      } else {
        synonyms.push(synonym);
      }
    });
    const visibleSyns = synonyms.slice(0, this.state.maxSynonyms);
    const visibleMisapplied = misappliedNames.slice(0, this.state.maxSynonyms);
    const hiddenSyns = synonyms.slice(this.state.maxSynonyms);
    const hiddenMisapplied = misappliedNames.slice(this.state.maxSynonyms);

    return (
      <>
        {
          synonyms.length > 0 ? (
            <div className={'synonym-items row dashed-border py-1'}>
              <div className="col font-weight-bold char-label">Synonyms</div>
              <div className="synonym-list col">
                <span className="short-list">
                  {visibleSyns.length > 0 &&
                    Object.entries(visibleSyns)
                      .map(([key, obj]) => {
                        return (
                          <span key={key} className={'synonym-item'}>
                            <span className={'synonym-sciname'}>{obj.sciname}</span>
                            <span className={'synonym-author'}> {obj.author}</span>
                            {
                              obj.nomenclaturalStatus ?
                              <span className={'synonym-author'}> ({obj.nomenclaturalStatus})</span> :
                              null
                            }
                          </span>
                        );
                      })
                      .reduce((prev, curr) => [prev, ', ', curr])}
                  {hiddenSyns.length > 0 && !this.state.showSynonyms ? '...' : ''}
                </span>

                <span className="full-list" hidden={!this.state.showSynonyms}>
                  {hiddenSyns.length > 0 &&
                    Object.entries(hiddenSyns)
                      .map(([key, obj]) => {
                        return (
                          <span key={key} className={'synonym-item'}>
                            <span className={'synonym-sciname'}>{obj.sciname}</span>
                            <span className={'synonym-author'}> {obj.author}</span>
                            {
                              obj.nomenclaturalStatus ?
                              <span className={'synonym-author'}> ({obj.nomenclaturalStatus})</span> :
                              null
                            }
                          </span>
                        );
                      })
                      .reduce((prev, curr) => [prev, ', ', curr])}
                </span>

                {synonyms.length > this.state.maxSynonyms && (
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
          ): (<></>)
        }
        {
          misappliedNames.length > 0 ? (
            <div className={'synonym-items row dashed-border py-1'}>
              <div className="col font-weight-bold char-label">Misapplied Names</div>
              <div className="synonym-list col">
                <span className="short-list">
                  {visibleMisapplied.length > 0 &&
                    Object.entries(visibleMisapplied)
                      .map(([key, obj]) => {
                        return (
                          <span key={key} className={'synonym-item'}>
                            <span className={'synonym-sciname'}>{obj.sciname}</span>
                            <span className={'synonym-author'}> {obj.author}</span>
                            {
                              obj.nomenclaturalStatus ?
                              <span className={'synonym-author'}> ({obj.nomenclaturalStatus})</span> :
                              null
                            }
                          </span>
                        );
                      })
                      .reduce((prev, curr) => [prev, ', ', curr])}
                  {hiddenMisapplied.length > 0 && !this.state.showMisappliedNames ? '...' : ''}
                </span>

                <span className="full-list" hidden={!this.state.showMisappliedNames}>
                  {hiddenMisapplied.length > 0 &&
                    Object.entries(hiddenMisapplied)
                      .map(([key, obj]) => {
                        return (
                          <span key={key} className={'synonym-item'}>
                            <span className={'synonym-sciname'}>{obj.sciname}</span>
                            <span className={'synonym-author'}> {obj.author}</span>
                            {
                              obj.nomenclaturalStatus ?
                              <span className={'synonym-author'}> ({obj.nomenclaturalStatus})</span> :
                              null
                            }
                          </span>
                        );
                      })
                      .reduce((prev, curr) => [prev, ', ', curr])}
                </span>

                {misappliedNames.length > this.state.maxSynonyms && (
                  <span>
                    <div className="up-down-toggle">
                      <FontAwesomeIcon
                        icon={this.state.showMisappliedNames ? 'chevron-up' : 'chevron-down'}
                        onClick={this.toggleMisappliedNames}
                      />
                    </div>
                  </span>
                )}
              </div>
            </div>
          ) : (<></>)
        }
      </>
    );
  }
}
