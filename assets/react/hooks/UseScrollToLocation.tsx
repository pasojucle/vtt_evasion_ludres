import React, { useEffect, useRef } from "react";

export const useScrollToLocation = (data: unknown, hash: string) => {
  const scrolledRef = useRef<boolean>(false);
  const hashRef = useRef<string | null>(hash);

  useEffect(() => {
    if (hash) {
      if (hashRef.current !== hash) {
        hashRef.current = hash;
        scrolledRef.current = false;
      }

      if (!scrolledRef.current) {
        const id = hash.replace('#', '');
        const element = document.getElementById(id);
        if (element) {
            const headerOffset = 160;
            const elementPosition = element.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            window.scrollTo({top: offsetPosition, behavior: "smooth",});
            scrolledRef.current = true;
        }
      }
    }
  }, [data, hash]);
};