import React, { useState } from 'react';
import { Tab, Tabs, TabList, TabPanel } from 'react-tabs';
import { addGlossaryTooltips } from '../../common/glossary.js';

function DescriptionTabs(props) {
  const [tabIndex, setTabIndex] = useState(0);
  return (
    <Tabs className="description-tabs" selectedIndex={tabIndex} onSelect={(tabIndex) => setTabIndex(tabIndex)}>
      <TabList>
        {Object.entries(props.descriptions).map(([key, value]) => (
          <Tab key={key}>{value['caption']}</Tab>
        ))}
      </TabList>
      {Object.entries(props.descriptions).map(([key, value]) => {
        const source = !value['source']
          ? ''
          : '[ ' +
            (value['url']
              ? '<a href=' + value['url'] + " target='_blank' >" + value['source'] + '</a>'
              : value['source']) +
            ' ]';

        const description = Object.values(value['desc']).reduce((acc, desc) => {
          return acc + desc;
        }, '');
        const descWithGlossary = addGlossaryTooltips(description, props.glossary);

        return (
          <TabPanel key={key} forceRender>
            <h2 className="tabTitle" dangerouslySetInnerHTML={{ __html: value.caption }} />
            <div className="reference" dangerouslySetInnerHTML={{ __html: source }} />
            <div className="description" dangerouslySetInnerHTML={{ __html: descWithGlossary }} />
          </TabPanel>
        );
      })}
    </Tabs>
  );
}

export default DescriptionTabs;
