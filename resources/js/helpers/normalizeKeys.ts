export const normalizeKeys = <T>(data: T): T => {
    if (Array.isArray(data)) {
        return data.map((item) => normalizeKeys(item)) as unknown as T;
    } else if (data !== null && typeof data === 'object') {
        const normalizedObject: Record<string, unknown> = {};

        for (const key in data) {
            if (Object.prototype.hasOwnProperty.call(data, key)) {
                const normalizedKey = toCamelCase(key);
                normalizedObject[normalizedKey] = normalizeKeys((data as Record<string, unknown>)[key]);
            }
        }

        return normalizedObject as T;
    }

    return data;
}

const toCamelCase = (str: string): string => {
    str = str[0].toLowerCase() + str.slice(1);

    return str.replace(/[-_](\w)/g, (_, char) => char.toUpperCase());

};

