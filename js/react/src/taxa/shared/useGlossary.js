import { useState, useEffect } from 'react';
import httpGet from '../../common/httpGet.js';

export function useGlossary() {
  const [glossary, setGlossary] = useState({});

  useEffect(() => {
    const fetchGlossary = async () => {
      try {
        const res = await httpGet('../glossary/rpc/getterms.php');
        setGlossary(JSON.parse(res));
      } catch (err) {
        // just log this error and don't do anything for now, since the glossary isn't strictly
        // necessary for the functioning of the page
        console.error(err);
      }
    };
    fetchGlossary();
  }, []);

  return glossary;
}
