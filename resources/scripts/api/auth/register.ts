import http from '@/api/http';

export interface RegisterData {
    email: string;
    username: string;
    nameFirst: string;
    nameLast: string;
    recaptchaData?: string | null;
}

export default ({ email, username, nameFirst, nameLast, recaptchaData }: RegisterData): Promise<string> => {
    return new Promise((resolve, reject) => {
        http.get('/sanctum/csrf-cookie')
            .then(() =>
                http.post('/auth/register', {
                    email,
                    username,
                    name_first: nameFirst,
                    name_last: nameLast,
                    'g-recaptcha-response': recaptchaData,
                })
            )
            .then((response) => resolve(response.data.status || ''))
            .catch(reject);
    });
};
