import { useState, useEffect } from 'react';
import httpGet from '../../common/httpGet.js';

export function useTaxonApi(tid, type) {
  const [data, setData] = useState(null);
  const [apiError, setApiError] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const api = type
          ? `./rpc/api.php?taxon=${tid}&type=${type}`
          : `./rpc/api.php?taxon=${tid}`;
        const res = JSON.parse(await httpGet(api));
        setData(res);
      } catch (err) {
        console.error(err);
        setApiError(true);
      } finally {
        setIsLoading(false);
      }
    };
    fetchData();
  }, [tid, type]);

  return { data, apiError, isLoading };
}
