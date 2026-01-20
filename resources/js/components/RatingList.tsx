import { ReactNode, useEffect, useState } from 'react';
import { Question } from '@/types';
import Answer from '@/routes/api/answer';
import Loading from '@/components/loading';
import { useLang } from '@/hooks/useLang';
import { Separator } from '@/components/ui/separator';
import StarRating from '@/components/StarRating';

export function RatingList({gameId}: {gameId: string}): ReactNode {
    const {locale} = useLang();
    const [questions, setQuestions] = useState<Question[]>([]);
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [answers, setAnswers] = useState([]);
    const loadAnswers = () => {
        setIsLoading(true);
        fetch(Answer.get(gameId).url)
            .then((res) => res.json())
            .then((data) => {
                setQuestions(() => data.questions);
                setAnswers(() => data.answers);
            })
            .then(() => setIsLoading(false));
    }

    // eslint-disable-next-line react-hooks/set-state-in-effect
    useEffect(loadAnswers, [gameId]);

    return isLoading ? (
        <Loading />
    ) : (
        <div>
            {questions.map((question, i) => (
                <>
                    <div className="grid grid-cols-3 rounded border p-2">
                        <div className="col-span-2">{question.translations[locale]}</div>
                        <div className="col-span-1"><StarRating /></div>

                    </div>
                    {i !== questions.length-1 && <Separator />}
                </>
            ))}
        </div>
    );
}
